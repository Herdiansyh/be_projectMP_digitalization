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

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Evaluation::with(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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
            $user->load(['approverSectionHead', 'approverManager']);

            $evaluation = DB::transaction(function () use ($request, $user) {
                return Evaluation::create([
                    'employee_id' => $request->employee_id,
                    'department_id' => $request->department_id,
                    'department_head_id' => $request->department_head_id,
                    'leader_id' => $user->id,
                    'section_head_id' => $user->approverSectionHead?->id,
                    'manager_id' => $user->approverManager?->id,
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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

        $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            $evaluation->status = 'rejected';
            $evaluation->current_stage = 'leader';
            $evaluation->save();

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => $evaluation->current_stage === 'leader' ? ($roleName === 'Manager' ? 'manager' : 'section_head') : $roleName,
                'user_id' => $user->id,
                'action' => 'reject',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $evaluation->load(['employee', 'scores.criteria', 'recommendation', 'approvals']);

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

            // Only Leader who created it can delete it, and only if it's still in draft
            if ($roleName !== 'Leader' || $evaluation->leader_id !== $user->id) {
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

}
