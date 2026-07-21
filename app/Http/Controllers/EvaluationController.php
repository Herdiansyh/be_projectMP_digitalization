<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Http\Requests\UpdateEvaluationScoresRequest;
use App\Http\Requests\UpdateEvaluationRecommendationRequest;
use App\Http\Resources\EvaluationResource;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationScore;
use App\Models\EvaluationRecommendation;
use App\Models\EvaluationApproval;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Relasi standar yang selalu di-load untuk response EvaluationResource.
     * `leader`, `sectionHead`, `manager` ditambahkan agar frontend bisa
     * menampilkan nama approver tanpa lookup ID manual.
     */
    private const FULL_RELATIONS = [
        'employee',
        'scores.criteria',
        'recommendation',
        'approvals',
        'leader',
        'sectionHead',
        'manager',
    ];

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Evaluation::with(self::FULL_RELATIONS);

            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Admin' && $roleName !== 'HR Admin') {
                if ($roleName === 'Leader') {
                    $query->where('leader_id', $user->id);
                } elseif ($roleName === 'Section Head') {
                    $query->where('section_head_id', $user->id);
                } elseif ($roleName === 'Manager') {
                    $query->where('manager_id', $user->id);
                } else {
                    return $this->errorResponse('Unauthorized access to evaluations', 403);
                }
            }

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $evaluations = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

            return $this->successResponse(
                EvaluationResource::collection($evaluations)->response()->getData(true),
                'Evaluations retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $user->load(['approverSectionHead']);

            $evaluation = DB::transaction(function () use ($request, $user) {
                return Evaluation::create([
                    'employee_id' => $request->employee_id,
                    'department_id' => $request->department_id,
                    'department_head_id' => $request->department_head_id,
                    'leader_id' => $user->id,
                    'section_head_id' => $user->approverSectionHead?->id,
                    // manager_id SENGAJA tidak diisi di sini. Manager yang akan
                    // meninjau evaluasi ini baru ditentukan saat Section Head
                    // approve — diambil dari approver_manager_id milik Section
                    // Head yang bertindak, bukan dari Leader. Lihat method approve().
                    'manager_id' => null,
                    'npk' => $request->npk,
                    'jabatan' => $request->jabatan,
                    'join_date' => $request->join_date,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'pkwt' => $request->pkwt,
                    'status' => 'draft',
                    'current_stage' => 'leader',
                ]);
            });

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Evaluation $evaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Admin' && $roleName !== 'HR Admin') {
                if ($roleName === 'Leader' && $evaluation->leader_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access to this evaluation', 403);
                }
                if ($roleName === 'Section Head' && $evaluation->section_head_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access to this evaluation', 403);
                }
                if ($roleName === 'Manager' && $evaluation->manager_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access to this evaluation', 403);
                }
            }

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Leader' || $evaluation->leader_id !== $user->id || $evaluation->current_stage !== 'leader') {
                return $this->errorResponse('Evaluation is locked for updates', 403);
            }

            $evaluation->update($request->only([
                'department_id',
                'department_head_id',
                'section_head_id',
                'manager_id',
                'npk',
                'jabatan',
                'join_date',
                'start_date',
                'end_date',
                'pkwt',
                'reminder_date',
                'reminder_note',
            ]));

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function updateScores(UpdateEvaluationScoresRequest $request, Evaluation $evaluation): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;
        $filledByRole = match ($roleName) {
            'Leader' => 'leader',
            'Section Head' => 'section_head',
            'Manager' => 'manager',
            'Admin'=> 'leader',
            default => null,
        };

        $allowedStage = match ($filledByRole) {
            'leader' => $evaluation->leader_id === $user->id && $evaluation->current_stage === 'leader',
            'section_head' => $evaluation->section_head_id === $user->id && $evaluation->current_stage === 'section_head',
            'manager' => $evaluation->manager_id === $user->id && $evaluation->current_stage === 'manager',
            default => false,
        };

        if (!$allowedStage) {
            return $this->errorResponse('Evaluation is locked for score updates', 403);
        }

        foreach ($request->scores as $item) {
            EvaluationScore::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'criteria_id' => $item['criteria_id'],
                    'filled_by_role' => $filledByRole,
                ],
                [
                    'score' => $item['score'],
                    'filled_by_user_id' => $user->id,
                ]
            );
        }

        $evaluation->load(self::FULL_RELATIONS);

        return $this->successResponse(
            new EvaluationResource($evaluation),
            'Evaluation scores updated successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

    public function updateRecommendation(UpdateEvaluationRecommendationRequest $request, Evaluation $evaluation): JsonResponse
    {
        try {
            $recommendation = EvaluationRecommendation::updateOrCreate(
                ['evaluation_id' => $evaluation->id],
                [
                    'employee_status' => $request->employee_status,
                    'extend_pkwt' => $request->boolean('extend_pkwt', false),
                    'pkwt_number' => $request->pkwt_number,
                    'extend_months' => $request->extend_months,
                    'notes' => $request->notes,
                    'created_by' => $request->created_by ?? Auth::id(),
                ]
            );

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation recommendation updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function submit(Evaluation $evaluation): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if ($roleName !== 'Leader' || $evaluation->leader_id !== $user->id) {
            return $this->errorResponse('Unauthorized to submit evaluation', 403);
        }

        if ($evaluation->current_stage !== 'leader') {
            return $this->errorResponse('Evaluation cannot be submitted from this stage', 422);
        }

        if (empty($evaluation->pkwt)) {
            return $this->errorResponse('PKWT is required before submitting', 422);
        }

        if (empty($evaluation->section_head_id)) {
            return $this->errorResponse('You do not have an Approver Section Head assigned. Please contact Admin to set this up before submitting.', 422);
        }

        $evaluation->load('recommendation');
        if (!$evaluation->recommendation || empty($evaluation->recommendation->employee_status)) {
            return $this->errorResponse('Recommendation is required before submitting', 422);
        }

        $evaluation->status = 'submitted_to_section_head';
        $evaluation->current_stage = 'section_head';
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'leader',
            'user_id' => $user->id,
            'action' => 'submit',
            'notes' => null,
            'acted_at' => now(),
        ]);

        $evaluation->load(self::FULL_RELATIONS);

        return $this->successResponse(
            new EvaluationResource($evaluation),
            'Evaluation submitted to section head successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

    public function approve(Request $request, Evaluation $evaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            $allowed = match ($evaluation->current_stage) {
                'section_head' => $roleName === 'Section Head' && $evaluation->section_head_id === $user->id,
                'manager' => $roleName === 'Manager' && $evaluation->manager_id === $user->id,
                default => false,
            };

            if (!$allowed) {
                return $this->errorResponse('Unauthorized to approve this evaluation', 403);
            }

            if ($evaluation->current_stage === 'section_head') {
                // Manager tujuan forward DITENTUKAN DI SINI, saat Section Head
                // approve — diambil dari approver_manager_id milik Section Head
                // yang sedang bertindak (bukan dari Leader yang membuat evaluasi).
                $user->loadMissing('approverManager');

                if (!$user->approverManager) {
                    return $this->errorResponse(
                        'You do not have an Approver Manager assigned. Please contact Admin to set this up before approving.',
                        422
                    );
                }

                $evaluation->manager_id = $user->approverManager->id;
                $evaluation->status = 'reviewed_by_section_head';
                $evaluation->current_stage = 'manager';
            } elseif ($evaluation->current_stage === 'manager') {
                $evaluation->status = 'approved';
                $evaluation->current_stage = 'done';
            }
            $evaluation->save();

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => $evaluation->current_stage === 'done' ? 'manager' : 'section_head',
                'user_id' => $user->id,
                'action' => 'approve',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation approved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function reject(Request $request, Evaluation $evaluation): JsonResponse
    {
        try {
            $request->validate([
                'notes' => 'required|string',
            ]);

            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            $allowed = match ($evaluation->current_stage) {
                'section_head' => $roleName === 'Section Head' && $evaluation->section_head_id === $user->id,
                'manager' => $roleName === 'Manager' && $evaluation->manager_id === $user->id,
                default => false,
            };

            if (!$allowed) {
                return $this->errorResponse('Unauthorized to reject this evaluation', 403);
            }

            $rejectedFromStage = $evaluation->current_stage;

            $evaluation->status = 'rejected';
            $evaluation->current_stage = 'leader';
            $evaluation->save();

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => $rejectedFromStage === 'manager' ? 'manager' : 'section_head',
                'user_id' => $user->id,
                'action' => 'reject',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse(
                new EvaluationResource($evaluation),
                'Evaluation rejected successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Evaluation $evaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            $isAdmin = in_array($roleName, ['Admin']);
            $isOwnerLeader = $roleName === 'Leader' && $evaluation->leader_id === $user->id;

            // Admin bisa hapus evaluasi apapun (konsisten dengan policy action lain).
            // Leader hanya bisa hapus draft yang dia sendiri buat.
            if (!$isAdmin && !$isOwnerLeader) {
                return $this->errorResponse('Unauthorized to delete this evaluation', 403);
            }

            if ($evaluation->status !== 'draft') {
                return $this->errorResponse('Only draft evaluations can be deleted', 422);
            }

            $evaluation->delete();

            return $this->successResponse(null, 'Evaluation deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function pendingTriggers(Request $request): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if (!in_array($roleName, ['Leader', 'Admin', 'HR Admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $query = Employee::query()
            ->where('employment_type', 'contract')
            ->whereNotNull('end_contract')
            ->whereBetween('end_contract', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            // belum ada evaluation utk periode kontrak SAAT INI (dibuat dalam 60 hari terakhir)
            ->whereDoesntHave('evaluations', function ($q) {
                $q->where('created_at', '>=', now()->subDays(60));
            });

        if ($roleName === 'Leader') {
            if ($user->area_id) {
                $query->where('area_id', $user->area_id);
            } else {
                $query->whereRaw('1 = 0'); // Jika Leader tidak memiliki area_id, jangan tampilkan apa-apa
            }
        }

        $employees = $query->orderBy('end_contract')->get([
            'id', 'npk', 'name', 'jabatan', 'department_id', 'section_id',
            'join_date', 'start_contract', 'end_contract', 'employment_type',
        ]);

        return $this->successResponse($employees, 'Pending evaluation triggers retrieved successfully');
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

public function forwardToHrAdmin(Request $request, Evaluation $evaluation): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if ($roleName !== 'Section Head' || $evaluation->section_head_id !== $user->id) {
            return $this->errorResponse('Unauthorized to forward this evaluation', 403);
        }

        if ($evaluation->status !== 'approved' || $evaluation->current_stage !== 'done') {
            return $this->errorResponse('Evaluation must be fully approved before forwarding to HR Admin', 422);
        }

        $evaluation->status = 'forwarded_to_hr_admin';
        $evaluation->current_stage = 'hr_admin';
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'section_head',
            'user_id' => $user->id,
            'action' => 'forward_to_hr_admin',
            'notes' => $request->input('notes'),
            'acted_at' => now(),
        ]);

        $evaluation->load(self::FULL_RELATIONS);

        return $this->successResponse(
            new EvaluationResource($evaluation),
            'Evaluation forwarded to HR Admin successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

public function extendContract(Request $request, Evaluation $evaluation): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if (!in_array($roleName, ['Admin', 'HR Admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($evaluation->current_stage !== 'hr_admin') {
            return $this->errorResponse('Evaluation is not pending HR Admin decision', 422);
        }

        $request->validate([
            'new_end_contract' => 'required|date|after:today',
            'pkwt_number' => 'nullable|string',
            'extend_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::find($evaluation->employee_id);

        DB::transaction(function () use ($request, $evaluation, $employee, $user) {
            $employee->contractExtensions()->create([
                'evaluation_id' => $evaluation->id,
                'previous_end_contract' => $employee->end_contract,
                'new_end_contract' => $request->new_end_contract,
                'pkwt_number' => $request->pkwt_number,
                'extend_months' => $request->extend_months,
                'notes' => $request->notes,
                'extended_by' => $user->id,
            ]);

            $employee->update(['end_contract' => $request->new_end_contract]);

            $evaluation->update([
                'status' => 'completed_extended',
                'current_stage' => 'completed',
            ]);

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => 'hr_admin',
                'user_id' => $user->id,
                'action' => 'extend_contract',
                'notes' => $request->notes,
                'acted_at' => now(),
            ]);
        });

        $evaluation->load(self::FULL_RELATIONS);

        return $this->successResponse(new EvaluationResource($evaluation), 'Contract extended successfully');
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

public function closeContract(Request $request, Evaluation $evaluation): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if (!in_array($roleName, ['Admin', 'HR Admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($evaluation->current_stage !== 'hr_admin') {
            return $this->errorResponse('Evaluation is not pending HR Admin decision', 422);
        }

        $request->validate([
            'action' => 'required|in:deactivate,delete',
            'reason' => 'nullable|string',
        ]);

        $employee = Employee::find($evaluation->employee_id);

        DB::transaction(function () use ($request, $evaluation, $employee, $user) {
            if ($request->action === 'deactivate') {
                $employee->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivated_reason' => $request->reason ?? 'Contract ended, not extended',
                ]);
            } else {
                $employee->delete();
            }

            $evaluation->update([
                'status' => 'completed_not_extended',
                'current_stage' => 'completed',
            ]);

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => 'hr_admin',
                'user_id' => $user->id,
                'action' => 'close_contract_' . $request->action,
                'notes' => $request->reason,
                'acted_at' => now(),
            ]);
        });

        $evaluation->load(self::FULL_RELATIONS);

        return $this->successResponse(new EvaluationResource($evaluation), 'Contract closed successfully');
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

public function pendingHrDecisions(Request $request): JsonResponse
{
    try {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        if (!in_array($roleName, ['Admin', 'HR Admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $query = Evaluation::with(self::FULL_RELATIONS)
            ->where('current_stage', 'hr_admin')
            ->where('status', 'forwarded_to_hr_admin');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $evaluations = $query->orderBy('updated_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            EvaluationResource::collection($evaluations)->response()->getData(true),
            'Pending HR Admin decisions retrieved successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}
}