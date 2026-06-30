<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FptkController extends Controller
{
    /**
     * Display a listing of the requisitions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
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
        $user     = auth()->user()->load(['approverManager', 'approverDivision', 'approverDirector']);
        $manager  = $user->approverManager?->name;
        $division = $user->approverDivision?->name;
        $director = $user->approverDirector?->name;

        if ($manager) {
            $initialStatus = 'Menunggu Approval Manager';
        } elseif ($division) {
            $initialStatus = 'Menunggu Approval Division Head';
        } elseif ($director) {
            $initialStatus = 'Menunggu Approval Director';
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
    $requisition = Requisition::with('replacementEmployee:id,npk,name')
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
     * HR Admin memproses FPTK yang sudah Approved.
     * Mengubah approval_status menjadi "Processed HRD".
     */
    public function processHrd(string $noReq): JsonResponse
    {
        $user = auth()->user();

        // Hanya HR Admin yang boleh
        if ($user->roleLevel?->name !== 'HR Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HR Admin yang dapat memproses FPTK.',
            ], 403);
        }

        $requisition = Requisition::findOrFail($noReq);

        // Hanya FPTK dengan status Approved yang bisa diproses
        if ($requisition->approval_status !== 'Approved') {
            return response()->json([
                'success' => false,
                'message' => 'FPTK hanya dapat diproses jika sudah berstatus Approved.',
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
            'message' => "FPTK {$noReq} berhasil diproses oleh HRD.",
            'data'    => $requisition,
        ]);
    }

    /**
     * Get requisitions pending approval for the current user.
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = Requisition::orderBy('request_date', 'desc');

        if ($user->roleLevel?->name === 'Director') {
            $query->byStatus('Menunggu Approval Director')->byDirector($user->name);
        } elseif ($user->roleLevel?->name === 'Division Head') {
            $query->byStatus('Menunggu Approval Division Head')->byDivision($user->name);
        } elseif ($user->roleLevel?->name === 'Manager') {
            $query->byStatus('Menunggu Approval Manager')->byManager($user->name);
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
        $user = auth()->user();

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
         $requisition = Requisition::with('replacementEmployee:id,npk,name')  // ← tambah with
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
        $user = auth()->user();
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