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
        ] : null);

        return $subject;
    }


    public function matrixForSubject(Request $request): JsonResponse
    {
        $subject = $this->resolveSubject($request->subject_type, $request->subject_id);

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Candidate not found.'], 404);
        }

        if (!$this->isWithinLeaderScope($subject)) {
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
            ]);

            $rows = collect($request->scores)->map(fn ($s) => [
                'assessment_id' => $assessment->id,
                'checkpoint_id' => $s['checkpoint_id'],
                'point'         => $s['point'],
            ])->all();

            AssessmentScore::insert($rows);

            return $assessment;
        });

        return response()->json([
            'success' => true,
            'message' => "Assessment for {$subject->name} saved.",
            'data'    => $assessment->fresh(['scores.checkpoint.category']),
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
    $history = EmployeeAssessment::with(['matrix', 'assessor:id,name', 'scores.checkpoint.category'])
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
    // Group scores by category, hitung total & average per kategori
    $categoryScores = $assessment->scores
        ->groupBy(fn ($score) => $score->checkpoint->category_id)
        ->map(function ($scoresInCategory) {
            $category = $scoresInCategory->first()->checkpoint->category;
            $totalPoint = $scoresInCategory->sum(
                fn ($s) => $s->point * $s->checkpoint->weight
            );
            $checkpointCount = $scoresInCategory->count();

            return [
                'category_id'      => $category->id,
                'category_name'    => $category->name,
                'total_point'      => $totalPoint,
                'checkpoint_count' => $checkpointCount,
                'average'          => $checkpointCount > 0
                    ? round($totalPoint / $checkpointCount, 2)
                    : 0,
            ];
        })
        ->values();

    // Skor akhir = rata-rata sederhana dari average tiap kategori
    $finalScore = $categoryScores->count() > 0
        ? round($categoryScores->avg('average'), 2)
        : 0;

    return [
        'id'              => $assessment->id,
        'period_label'    => $assessment->period_label,
        'assessed_at'     => $assessment->assessed_at,
        'notes'           => $assessment->notes,
        'final_score'     => $finalScore,
        'category_scores' => $categoryScores,
        'assessor'        => [
            'id'   => $assessment->assessor->id,
            'name' => $assessment->assessor->name,
        ],
    ];
}
private function resolveSubject(?string $type, ?int $id): Employee|Intern|null
    {
        if (!$type || !$id) return null;

        return $type === 'employee'
            ? Employee::find($id)
            : Intern::find($id);
    }
    private function isWithinLeaderScope(Employee|Intern $subject): bool
    {
        $user = Auth::user();

        // Admin/HR Admin tetap boleh akses semua (untuk keperluan monitoring),
        // hanya Leader yang benar-benar dibatasi per area.
        if (in_array($user->roleLevel?->name, ['Admin', 'HR Admin'])) {
            return true;
        }

        return $user->area_id !== null && $user->area_id === $subject->area_id;
    }
}