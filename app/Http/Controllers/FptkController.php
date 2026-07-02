<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAreaLineRequest;
use App\Http\Requests\AssignManpowerRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Intern;
use App\Models\Requisition;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FptkController extends Controller
{
    /**
     * Display a listing of the requisitions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Requisition::orderBy('request_date', 'desc');

        if ($user) {
            $roleName = $user->roleLevel?->name;
            if (!in_array($roleName, ['Admin', 'HR Admin'])) {
                $query->where(function ($q) use ($user) {
                    $q->where('requester_name', $user->name)
                      ->orWhere('manager', $user->name)
                      ->orWhere('division', $user->name)
                      ->orWhere('director', $user->name)
                      ->orWhere('supervisor', $user->name);
                });
            }
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('exclude_status')) {
            $excludeStatuses = explode(',', $request->exclude_status);
            $query->whereNotIn('approval_status', $excludeStatuses);
        }

        if ($request->has('manager')) {
            $query->byManager($request->manager);
        }

        if ($request->has('division')) {
            $query->byDivision($request->division);
        }

        if ($request->has('director')) {
            $query->byDirector($request->director);
        }

        if ($request->has('supervisor')) {
            $query->bySupervisor($request->supervisor);
        }

        // ?needs_area_line=1 → used by FE for the "needs area/line" row badge/filter
        if ($request->boolean('needs_area_line')) {
            $query->needsAreaLine();
        }

        $perPage = min((int) ($request->per_page ?? 15), 100);
        $requisitions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $requisitions,
        ]);
    }

    /**
     * Store a newly created requisition in storage.
     */
   public function store(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'requester_name'          => 'required|string|max:255',
        'request_date'            => 'required|date',
        'group'                   => 'nullable|string|max:255',
        'department'              => 'nullable|string|max:255',
        'section'                 => 'nullable|string|max:255',
        'type'                    => 'nullable|string|max:255',
        'position'                => 'nullable|string|max:255',
        'status'                  => 'nullable|string|max:255',
        'duration'                => 'nullable|string|max:255',
        'level'                   => 'nullable|string|max:255',
        'cost_employee'           => 'nullable|string|max:255',
        'fulfilment_time'         => 'nullable|string|max:255',
        'education'               => 'nullable|string|max:255',
        'max_age'                 => 'nullable|integer|min:18',
        'min_experience'          => 'nullable|integer|min:0',
        'technical_skill'         => 'nullable|array',
        'soft_skill'              => 'nullable|array',
        'description'             => 'nullable|string',
        'cost_center'             => 'nullable|string|max:255',
        'objective'               => 'nullable|string|max:255',
        'reason'                  => 'nullable|string',
        'employee_out'            => 'nullable|string|max:255',
        'replacement_employee_id' => 'nullable|exists:employees,id',
        'apprenticeship_period'   => 'nullable|boolean',
        'manpower_plan'           => 'nullable|string',
        'unplanned_reason'        => 'nullable|string',
        'supervisor'              => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $requisition = DB::transaction(function () use ($request) {
        // ── Generate no_req ──────────────────────────────────────────
        $prefix = 'FPTK-' . now()->format('Ymd') . '-';

        $last = Requisition::where('no_req', 'like', $prefix . '%')
            ->orderBy('no_req', 'desc')
            ->lockForUpdate()
            ->first();

        $newNumber = $last
            ? str_pad((int) substr($last->no_req, -4) + 1, 4, '0', STR_PAD_LEFT)
            : '0001';

        $noReq = $prefix . $newNumber;

        // ── Approver chain ───────────────────────────────────────────
        $user = User::with([
            'approverManager',
            'approverDivision',
            'approverDirector',
        ])->findOrFail(Auth::id());

        $manager  = $user->approverManager?->name;
        $division = $user->approverDivision?->name;
        $director = $user->approverDirector?->name;

        if ($manager) {
            $initialStatus = 'Waiting for Manager Approval';
        } elseif ($division) {
            $initialStatus = 'Waiting for Division Head Approval';
        } elseif ($director) {
            $initialStatus = 'Waiting for Director Approval';
        } else {
            $initialStatus = 'Approved';
        }

        // ── Create requisition ───────────────────────────────────────
        return Requisition::create([
            'no_req'                  => $noReq,
            'requester_name'          => $request->requester_name,
            'request_date'            => $request->request_date,
            'group'                   => $request->group,
            'department'              => $request->department,
            'section'                 => $request->section,
            'type'                    => $request->type,
            'position'                => $request->position,
            'status'                  => $request->status,
            'duration'                => $request->duration,
            'level'                   => $request->level,
            'cost_employee'           => $request->cost_employee,
            'fulfilment_time'         => $request->fulfilment_time,
            'education'               => $request->education,
            'max_age'                 => $request->max_age,
            'min_experience'          => $request->min_experience,
            'technical_skill'         => $request->technical_skill,
            'soft_skill'              => $request->soft_skill,
            'description'             => $request->description,
            'cost_center'             => $request->cost_center,
            'objective'               => $request->objective,
            'reason'                  => $request->reason,
            'employee_out'            => $request->employee_out,
            'replacement_employee_id' => $request->replacement_employee_id,
            'apprenticeship_period'   => $request->boolean('apprenticeship_period', false),
            'manpower_plan'           => $request->manpower_plan,
            'unplanned_reason'        => $request->unplanned_reason,
            'manager'                 => $manager,
            'division'                => $division,
            'director'                => $director,
            'supervisor'              => $request->supervisor,
            'approval_status'         => $initialStatus,
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Requisition created successfully',
        'data'    => $requisition,
    ], 201);
}
    /**
     * Display the specified requisition.
     */
public function show(string $noReq): JsonResponse
{
    $requisition = Requisition::with(['replacementEmployee:id,npk,name', 'assignedEmployee:id,npk,name', 'assignedIntern:id,npk,name'])
        ->findOrFail($noReq);

    if (!$this->canAccessRequisition($requisition)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access to this FPTK',
        ], 403);
    }

    return response()->json([
        'success' => true,
        'data'    => $requisition,
    ]);
}
    /**
     * Update the specified requisition in storage.
     */
    public function update(Request $request, string $noReq): JsonResponse
    {
        $requisition = Requisition::findOrFail($noReq);

        if (!$this->canAccessRequisition($requisition)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this FPTK',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'requester_name'  => 'sometimes|required|string|max:255',
            'request_date'    => 'sometimes|required|date',
            'group'           => 'nullable|string|max:255',
            'department'      => 'nullable|string|max:255',
            'section'         => 'nullable|string|max:255',
            'type'            => 'nullable|string|max:255',
            'position'        => 'nullable|string|max:255',
            'status'          => 'nullable|string|max:255',
            'duration'        => 'nullable|string|max:255',
            'level'           => 'nullable|string|max:255',
            'cost_employee'   => 'nullable|string|max:255',
            'fulfilment_time' => 'nullable|string|max:255',
            'education'       => 'nullable|string|max:255',
            'max_age'         => 'nullable|integer|min:18',
            'min_experience'  => 'nullable|integer|min:0',
            'technical_skill' => 'nullable|array',
            'soft_skill'      => 'nullable|array',
            'description'     => 'nullable|string',
            'cost_center'     => 'nullable|string|max:255',
            'objective'       => 'nullable|string|max:255',
            'reason'          => 'nullable|string',
            'employee_out'    => 'nullable|string|max:255',
            'manpower_plan'   => 'nullable|string',
            'unplanned_reason'=> 'nullable|string',
            'supervisor'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $requisition->update($request->only([
            'requester_name', 'request_date', 'group', 'department',
            'section', 'type', 'position', 'status', 'duration', 'level',
            'cost_employee', 'fulfilment_time', 'education', 'max_age',
            'min_experience', 'technical_skill', 'soft_skill', 'description',
            'cost_center', 'objective', 'reason', 'employee_out',
            'manpower_plan', 'unplanned_reason', 'supervisor',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Requisition updated successfully',
            'data'    => $requisition,
        ]);
    }

    /**
     * Remove the specified requisition from storage.
     */
    public function destroy(string $noReq): JsonResponse
    {
        $requisition = Requisition::findOrFail($noReq);

        if (!$this->canAccessRequisition($requisition)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this FPTK',
            ], 403);
        }

        $requisition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Requisition deleted successfully',
        ]);
    }

    /**
     * HR Admin processes an FPTK that has already been Approved.
     * Changes approval_status to "Processed HRD".
     */
    public function processHrd(string $noReq): JsonResponse
    {
        $user = Auth::user();

        // Only HR Admin is allowed
        if ($user->roleLevel?->name !== 'HR Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only HR Admin can process this FPTK.',
            ], 403);
        }

        $requisition = Requisition::findOrFail($noReq);

        // Only FPTK with Approved status can be processed
        if ($requisition->approval_status !== 'Approved') {
            return response()->json([
                'success' => false,
                'message' => 'FPTK can only be processed once it has Approved status.',
            ], 422);
        }

        $requisition->update([
            'approval_status'  => 'Processed HRD',
            'hrd_approved'     => true,
            'hrd_processed_at' => now(),
            'hrd_processed_by' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "FPTK {$noReq} has been successfully processed by HRD.",
            'data'    => $requisition,
        ]);
    }

    /**
     * HR Admin fills in the candidate's NPK, name, and contract dates
     * after CV screening is completed.
     *
     * Not yet inserted into employees/interns — waiting for the requester
     * to complete area/line (see assignAreaLine()).
     */
    public function assignManpower(AssignManpowerRequest $request, string $noReq): JsonResponse
    {
        $user = Auth::user();
        $requisition = Requisition::findOrFail($noReq);

        if ($requisition->approval_status !== 'Processed HRD') {
            return response()->json([
                'success' => false,
                'message' => 'FPTK must have Processed HRD status before manpower data can be filled in.',
            ], 422);
        }

        $requisition->update([
            'assigned_npk'            => $request->npk,
            'assigned_name'           => $request->name,
            'assigned_start_contract' => $request->start_contract,
            'assigned_end_contract'   => $request->end_contract,
            'hrd_assigned_at'         => now(),
            'hrd_assigned_by'         => $user->name,
        ]);

        // TODO: send a notification to the requester (the badge already
        // appears automatically via scopeNeedsAreaLine / the needs_area_line
        // accessor on the FPTK list).

        return response()->json([
            'success' => true,
            'message' => "Manpower data for FPTK {$noReq} has been saved. Waiting for the requester to complete area/line.",
            'data'    => $requisition,
        ]);
    }

    /**
     * Requester completes the area (and line if department is Manufacturing).
     * After this, the system automatically inserts the final record into
     * employees or interns depending on apprenticeship_period, then the
     * FPTK status becomes "Manpower Assigned".
     */
    public function assignAreaLine(AssignAreaLineRequest $request, string $noReq): JsonResponse
    {
        $requisition = Requisition::findOrFail($noReq);

        if (is_null($requisition->hrd_assigned_at)) {
            return response()->json([
                'success' => false,
                'message' => 'HRD has not yet filled in the NPK/contract data for this FPTK.',
            ], 422);
        }

        if (!is_null($requisition->employee_id) || !is_null($requisition->intern_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Area/line for this FPTK has already been filled in.',
            ], 422);
        }

        $requisition = DB::transaction(function () use ($requisition, $request) {
            $requisition->update([
                'assigned_area'       => $request->area,
                'assigned_line'       => $request->line,
                'area_line_filled_at' => now(),
            ]);

            $departmentId = Department::where('name', $requisition->department)->value('id');
            $sectionId    = Section::where('name', $requisition->section)->value('id');

           $commonAttributes = [
    'npk'            => $requisition->assigned_npk,
    'name'           => $requisition->assigned_name,
    'gender'         => 'male',      // ← required field, defaults to male since FPTK has no gender data
    'department_id'  => $departmentId,
    'section_id'     => $sectionId,
    'jabatan'        => $requisition->position,
    'start_contract' => $requisition->assigned_start_contract,
    'end_contract'   => $requisition->assigned_end_contract,
    'area'           => $requisition->assigned_area,
    'line'           => $requisition->assigned_line, // ← line is now saved as well
];
 
if ($requisition->apprenticeship_period) {
    $intern = Intern::create($commonAttributes);
    $requisition->intern_id = $intern->id;
} else {
    // Map FPTK status to employee employment_type
    // FPTK status may contain: Permanent, Contract, Magang, etc.
    $statusMap = [
        'Permanent' => 'permanent',
        'Contract'  => 'contract',
        'Magang'    => 'contract', // fallback in case it occurs
    ];
    $employmentType = $statusMap[$requisition->status] ?? 'permanent';
 
    $employee = Employee::create(array_merge($commonAttributes, [
        'employment_type' => $employmentType,
        'status'          => 'active',
    ]));
    $requisition->employee_id = $employee->id;
}
 
// Final status once the process is complete
$requisition->approval_status = 'Manpower Assigned';
$requisition->save();

            return $requisition;
        });

        return response()->json([
            'success' => true,
            'message' => "FPTK {$noReq} completed — manpower record has been created.",
            'data'    => $requisition,
        ]);
    }

    /**
     * Get requisitions pending approval for the current user.
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = Requisition::orderBy('request_date', 'desc');

        if ($user->roleLevel?->name === 'Director') {
            $query->byStatus('Waiting for Director Approval')->byDirector($user->name);
        } elseif ($user->roleLevel?->name === 'Division Head') {
            $query->byStatus('Waiting for Division Head Approval')->byDivision($user->name);
        } elseif ($user->roleLevel?->name === 'Manager') {
            $query->byStatus('Waiting for Manager Approval')->byManager($user->name);
        }

        $perPage = min((int) ($request->per_page ?? 15), 100);
        $requisitions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $requisitions,
        ]);
    }

    /**
     * Return FPTKs where the current user has already taken action.
     */
    public function approvalHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query    = Requisition::orderBy('request_date', 'desc');
        $roleName = $user->roleLevel?->name;

        if ($roleName === 'Manager') {
            $query->where('manager', $user->name)->whereNotNull('manager_approved_at');
        } elseif ($roleName === 'Division Head') {
            $query->where('division', $user->name)->whereNotNull('division_approved_at');
        } elseif ($roleName === 'Director') {
            $query->where('director', $user->name)->whereNotNull('director_approved_at');
        } else {
            return response()->json([
                'success' => true,
                'data'    => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0],
            ]);
        }

        $requisitions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => $requisitions,
        ]);
    }

    /**
     * Get approvers grouped by role level.
     */
    public function getApprovers(): JsonResponse
    {
        $users = \App\Models\User::with('roleLevel')
            ->whereHas('roleLevel', fn($q) => $q->whereIn('name', ['Manager', 'Division Head', 'Director']))
            ->select('id', 'name', 'npk', 'role_level_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'managers'      => $users->filter(fn($u) => $u->roleLevel?->name === 'Manager')->values(),
                'division_heads'=> $users->filter(fn($u) => $u->roleLevel?->name === 'Division Head')->values(),
                'directors'     => $users->filter(fn($u) => $u->roleLevel?->name === 'Director')->values(),
            ],
        ]);
    }

    /**
     * Display the print view for the specified requisition.
     */
    public function printView(string $noReq)
    {
         $requisition = Requisition::with('replacementEmployee:id,npk,name')  // ← added relation
        ->findOrFail($noReq);

        if (is_string($requisition->technical_skill)) {
            $requisition->technical_skill = json_decode($requisition->technical_skill, true)
                ?: explode(',', $requisition->technical_skill);
        }
        if (is_string($requisition->soft_skill)) {
            $requisition->soft_skill = json_decode($requisition->soft_skill, true)
                ?: explode(',', $requisition->soft_skill);
        }

        return view('print.fptk', compact('requisition'));
    }

    /**
     * Check if user has access to the requisition.
     */
    private function canAccessRequisition(Requisition $requisition): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $roleName = $user->roleLevel?->name;
        if (in_array($roleName, ['Admin', 'HR Admin'])) return true;

        return in_array($user->name, [
            $requisition->requester_name,
            $requisition->manager,
            $requisition->division,
            $requisition->director,
            $requisition->supervisor,
        ]);
    }
}