<?php

namespace App\Http\Controllers;

use App\Models\AssessmentScore;
use App\Models\CompetencyMatrix;
use App\Models\Employee;
use App\Models\EmployeeAssessment;
use App\Models\Intern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeAssessmentController extends Controller
{
    public function assessableEmployees(): JsonResponse
    {
        $user = Auth::user();

        if (!$this->isAdmin($user) && !$this->isLeader($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform assessments.',
            ], 403);
        }

        if ($this->isAdmin($user)) {
            $employees = Employee::with(['station', 'line', 'area'])
                ->get()
                ->map(fn ($e) => $this->attachLatestAssessment($e, 'employee'));

            $interns = Intern::with(['station', 'line', 'area'])
                ->get()
                ->map(fn ($i) => $this->attachLatestAssessment($i, 'intern'));

            return response()->json([
                'success' => true,
                'data'    => $employees->concat($interns)->values(),
            ]);
        }

        // Leader: scoped to own area only
        if (is_null($user->area_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not assigned to any area yet. Please contact Admin.',
            ], 422);
        }

        $employees = Employee::with(['station', 'line', 'area'])
            ->where('area_id', $user->area_id)
            ->get()
            ->map(fn ($e) => $this->attachLatestAssessment($e, 'employee'));

        $interns = Intern::with(['station', 'line', 'area'])
            ->where('area_id', $user->area_id)
            ->get()
            ->map(fn ($i) => $this->attachLatestAssessment($i, 'intern'));

        return response()->json([
            'success' => true,
            'data'    => $employees->concat($interns)->values(),
        ]);
    }

    private function attachLatestAssessment(Employee|Intern $subject, string $type)
    {
        $fk = $type === 'employee' ? 'employee_id' : 'intern_id';

        $latest = EmployeeAssessment::where($fk, $subject->id)
            ->orderByDesc('assessed_at')
            ->first();

        $subject->setAttribute('subject_type', $type);
        $subject->setAttribute('latest_assessment', $latest ? [
            'id'           => $latest->id,
            'period_label' => $latest->period_label,
            'assessed_at'  => $latest->assessed_at,
            'final_score'  => $latest->final_score,
            'status'       => $latest->status,
        ] : null);

        return $subject;
    }

    public function matrixForSubject(Request $request): JsonResponse
{
    $subject = $this->resolveSubject($request->subject_type, $request->subject_id);

    if (!$subject) {
        return response()->json(['success' => false, 'message' => 'Candidate not found.'], 404);
    }

    $user = Auth::user();

    // QA boleh akses semua area (dia butuh matrix ini untuk review, bukan submit baru).
    // Leader/Admin tetap pakai aturan scope yang sudah ada.
    if (!$this->isQA($user) && !$this->isWithinLeaderScope($subject)) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    $matrix = CompetencyMatrix::with('categories.checkpoints')
        ->where('station_id', $subject->station_id)
        ->where('is_active', true)
        ->first();

    if (!$matrix) {
        return response()->json([
            'success' => false,
            'message' => 'No active competency matrix found for this station yet. Please ask Admin to set it up.',
        ], 404);
    }

    return response()->json(['success' => true, 'data' => $matrix]);
}

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_type'          => 'required|in:employee,intern',
            'subject_id'            => 'required|integer',
            'matrix_id'             => 'required|exists:competency_matrices,id',
            'period_label'          => 'required|string|max:255',
            'notes'                 => 'nullable|string',
            'scores'                => 'required|array|min:1',
            'scores.*.checkpoint_id'=> 'required|exists:competency_checkpoints,id',
            'scores.*.point'        => 'required|integer|min:0|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $subject = $this->resolveSubject($request->subject_type, $request->subject_id);

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Candidate not found.'], 404);
        }

        if (!$this->isWithinLeaderScope($subject)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only assess manpower within your own area.',
            ], 403);
        }

        $matrix = CompetencyMatrix::with('categories.checkpoints')->findOrFail($request->matrix_id);
        $requiredCheckpointIds = $matrix->categories
            ->flatMap(fn ($c) => $c->checkpoints)
            ->pluck('id');

        $submittedCheckpointIds = collect($request->scores)->pluck('checkpoint_id');

        $missing = $requiredCheckpointIds->diff($submittedCheckpointIds);
        if ($missing->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Not all checkpoints in this matrix have been scored.',
                'missing_checkpoint_ids' => $missing->values(),
            ], 422);
        }

        $assessment = DB::transaction(function () use ($request, $subject) {
    $assessment = EmployeeAssessment::create([
        'employee_id'  => $request->subject_type === 'employee' ? $subject->id : null,
        'intern_id'    => $request->subject_type === 'intern' ? $subject->id : null,
        'matrix_id'    => $request->matrix_id,
        'assessed_by'  => Auth::id(),
        'period_label' => $request->period_label,
        'assessed_at'  => now(),
        'notes'        => $request->notes,
        'status'       => 'pending_QA',
    ]);

    $rows = collect($request->scores)->map(fn ($s) => [
        'assessment_id' => $assessment->id,
        'checkpoint_id' => $s['checkpoint_id'],
        'point'         => $s['point'],
        'source'        => 'leader',
    ])->all();

    AssessmentScore::insert($rows);

    return $assessment;
});


        return response()->json([
    'success' => true,
    'message' => "Assessment for {$subject->name} saved and forwarded to QA.",
    'data'    => $assessment->fresh(['scores.checkpoint.category']),
], 201);
    }

public function qaQueue(): JsonResponse
{
    $user = Auth::user();

    if (!$this->isAdmin($user) && !$this->isQA($user)) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to review assessments.',
        ], 403);
    }

    $pending = EmployeeAssessment::with([
            'matrix',
            'assessor:id,name',
            'employee.station', 'employee.line', 'employee.area',
            'intern.station', 'intern.line', 'intern.area',
            'scores.checkpoint.category',
        ])
        ->where('status', 'pending_QA')
        ->orderBy('assessed_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $pending->map(fn ($a) => [
            'id'                     => $a->id,
            'period_label'           => $a->period_label,
            'assessed_at'            => $a->assessed_at,
            'notes'                  => $a->notes,
            'assessor'               => $a->assessor,
            'subject'                => $a->subject,
            'subject_type'           => $a->employee_id ? 'employee' : 'intern',
            'matrix_id'              => $a->matrix_id,
            'leader_category_scores' => $a->leader_category_scores,
            'leader_scores'          => $a->scores
                ->where('source', 'leader')
                ->pluck('point', 'checkpoint_id'),
        ]),
    ]);
}

public function qaStore(Request $request, int $assessment): JsonResponse
{
    $user = Auth::user();

    if (!$this->isAdmin($user) && !$this->isQA($user)) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to review assessments.',
        ], 403);
    }

    $assessmentModel = EmployeeAssessment::with('matrix.categories.checkpoints')->find($assessment);

    if (!$assessmentModel) {
        return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
    }

    if ($assessmentModel->status !== 'pending_QA') {
        return response()->json([
            'success' => false,
            'message' => 'This assessment has already been reviewed by QA.',
        ], 422);
    }

    $validator = Validator::make($request->all(), [
        'scores'                 => 'required|array|min:1',
        'scores.*.checkpoint_id' => 'required|exists:competency_checkpoints,id',
        'scores.*.point'         => 'required|integer|min:0|max:4',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $requiredCheckpointIds = $assessmentModel->matrix->categories
        ->flatMap(fn ($c) => $c->checkpoints)
        ->pluck('id');

    $submittedCheckpointIds = collect($request->scores)->pluck('checkpoint_id');

    $missing = $requiredCheckpointIds->diff($submittedCheckpointIds);
    if ($missing->isNotEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Not all checkpoints in this matrix have been scored.',
            'missing_checkpoint_ids' => $missing->values(),
        ], 422);
    }

    DB::transaction(function () use ($request, $assessmentModel) {
        $rows = collect($request->scores)->map(fn ($s) => [
            'assessment_id' => $assessmentModel->id,
            'checkpoint_id' => $s['checkpoint_id'],
            'point'         => $s['point'],
            'source'        => 'qa',
        ])->all();

        AssessmentScore::insert($rows);

        $assessmentModel->update([
            'status' => 'approved',
            'qa_by'  => Auth::id(),
            'qa_at'  => now(),
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'QA review saved. This is now the final score.',
        'data'    => $assessmentModel->fresh(['scores.checkpoint.category']),
    ], 201);
}
public function history(Request $request): JsonResponse
{
    $subject = $this->resolveSubject($request->subject_type, $request->subject_id);

    if (!$subject) {
        return response()->json(['success' => false, 'message' => 'Candidate not found.'], 404);
    }

    if (!$this->isWithinLeaderScope($subject)) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    $fk = $request->subject_type === 'employee' ? 'employee_id' : 'intern_id';
    $history = EmployeeAssessment::with([
            'matrix',
            'assessor:id,name',
            'scores.checkpoint.category', 
        ])
        ->where($fk, $subject->id)
        ->orderBy('assessed_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $history->map(fn ($assessment) => $this->formatHistoryItem($assessment)),
    ]);
}

 private function formatHistoryItem(EmployeeAssessment $assessment): array
{
    return [
        'id'              => $assessment->id,
        'period_label'    => $assessment->period_label,
        'assessed_at'     => $assessment->assessed_at,
        'notes'           => $assessment->notes,
        'status'          => $assessment->status,
        'final_score'     => $assessment->final_score,
        'category_scores' => $assessment->category_scores,
        'assessor'        => [
            'id'   => $assessment->assessor->id,
            'name' => $assessment->assessor->name,
        ],
        'qa_reviewer' => $assessment->QaReviewer ? [
            'id'   => $assessment->QaReviewer->id,
            'name' => $assessment->QaReviewer->name,
        ] : null,
    ];
}
public function mySubmissions(): JsonResponse
{
    $user = Auth::user();

    if (!$this->isAdmin($user) && !$this->isLeader($user)) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to view this.',
        ], 403);
    }

    $assessments = EmployeeAssessment::with([
            'matrix',
            'QaReviewer:id,name',
            'employee.station', 'employee.line', 'employee.area',
            'intern.station', 'intern.line', 'intern.area',
        ])
        ->where('assessed_by', $user->id)
        ->orderByDesc('assessed_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $assessments->map(fn ($a) => [
            'id'           => $a->id,
            'period_label' => $a->period_label,
            'assessed_at'  => $a->assessed_at,
            'status'       => $a->status,
            'final_score'  => $a->final_score,
            'subject'      => $a->subject,
            'subject_type' => $a->employee_id ? 'employee' : 'intern',
            'qa_reviewer'  => $a->QaReviewer ? [
                'id'   => $a->QaReviewer->id,
                'name' => $a->QaReviewer->name,
            ] : null,
            'qa_at' => $a->qa_at,
        ]),
    ]);
}
public function showDetail(int $assessment): JsonResponse
{
    $user = Auth::user();

    $assessmentModel = EmployeeAssessment::with([
            'matrix.categories.checkpoints',
            'assessor:id,name',
            'QaReviewer:id,name',
            'scores',
        ])
        ->find($assessment);

    if (!$assessmentModel) {
        return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
    }

    // Hanya boleh dilihat oleh: yang submit (Leader pemilik), Admin, atau QA
    $isOwner = $assessmentModel->assessed_by === $user->id;
    if (!$isOwner && !$this->isAdmin($user) && !$this->isQA($user)) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id'              => $assessmentModel->id,
            'period_label'    => $assessmentModel->period_label,
            'assessed_at'     => $assessmentModel->assessed_at,
            'notes'           => $assessmentModel->notes,
            'status'          => $assessmentModel->status,
            'assessor'        => $assessmentModel->assessor,
            'qa_reviewer'     => $assessmentModel->QaReviewer,
            'qa_at'           => $assessmentModel->qa_at,
            'matrix'          => $assessmentModel->matrix,
            'leader_scores'   => $assessmentModel->scores->where('source', 'leader')->pluck('point', 'checkpoint_id'),
            'qa_scores'       => $assessmentModel->scores->where('source', 'qa')->pluck('point', 'checkpoint_id'),
            'category_scores' => $assessmentModel->category_scores, // hasil QA (final)
            'final_score'     => $assessmentModel->final_score,
        ],
    ]);
}

public function myReviews(): JsonResponse
{
    $user = Auth::user();

    if (!$this->isAdmin($user) && !$this->isQA($user)) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to view this.',
        ], 403);
    }

    $reviews = EmployeeAssessment::with([
            'matrix',
            'assessor:id,name',
            'employee.station', 'employee.line', 'employee.area',
            'intern.station', 'intern.line', 'intern.area',
        ])
        ->where('qa_by', $user->id)
        ->orderByDesc('qa_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $reviews->map(fn ($a) => [
            'id'           => $a->id,
            'period_label' => $a->period_label,
            'assessed_at'  => $a->assessed_at,
            'qa_at'        => $a->qa_at,
            'final_score'  => $a->final_score,
            'subject'      => $a->subject,
            'subject_type' => $a->employee_id ? 'employee' : 'intern',
            'assessor'     => [
                'id'   => $a->assessor->id,
                'name' => $a->assessor->name,
            ],
        ]),
    ]);
}

    private function resolveSubject(?string $type, ?int $id): Employee|Intern|null
    {
        if (!$type || !$id) return null;

        return $type === 'employee'
            ? Employee::find($id)
            : Intern::find($id);
    }

    /**
     * Admin ditentukan oleh kolom is_admin ATAU roleLevel->name === 'Admin'.
     */
    private function isAdmin($user): bool
    {
        return (bool) $user->is_admin || $user->roleLevel?->name === 'Admin';
    }

    /**
     * Leader ditentukan murni oleh roleLevel->name === 'Leader'.
     */
    private function isLeader($user): bool
    {
        return $user->roleLevel?->name === 'Leader';
    }

    private function isQA($user): bool
    {
        return $user->roleLevel?->name === 'Quality Assurance';
    }

    /**
     * Admin: akses semua subject, tanpa batas area.
     * Leader: hanya subject dengan area_id yang sama dengan miliknya.
     * Role lain: selalu ditolak, meski kebetulan punya area_id.
     */
    private function isWithinLeaderScope(Employee|Intern $subject): bool
    {
        $user = Auth::user();

        if ($this->isAdmin($user)) {
            return true;
        }

        if (!$this->isLeader($user)) {
            return false;
        }

        return $user->area_id !== null && $user->area_id === $subject->area_id;
    }

    
}