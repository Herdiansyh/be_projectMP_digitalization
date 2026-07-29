<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\InternEvaluation;
use App\Models\InternEvaluationScore;
use App\Models\InternEvaluationRecommendation;
use App\Models\InternEvaluationApproval;
use App\Models\Employee;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternEvaluationController extends Controller
{
    use ApiResponseTrait;

    private const FULL_RELATIONS = [
        'intern',
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
            $query = InternEvaluation::with(self::FULL_RELATIONS);

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
                    return $this->errorResponse('Unauthorized access to intern evaluations', 403);
                }
            }

            if ($request->filled('intern_id')) {
                $query->where('intern_id', $request->intern_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $evaluations = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

            return $this->successResponse($evaluations, 'Intern evaluations retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'intern_id' => 'required|exists:interns,id',
                'department_id' => 'nullable|exists:departments,id',
                'department_head_id' => 'nullable|exists:users,id',
                'npk' => 'nullable|string',
                'jabatan' => 'nullable|string',
                'join_date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $user = Auth::user();
            $user->load(['approverSectionHead']);

            $evaluation = DB::transaction(function () use ($request, $user) {
                return InternEvaluation::create([
                    'intern_id' => $request->intern_id,
                    'department_id' => $request->department_id,
                    'department_head_id' => $request->department_head_id,
                    'leader_id' => $user->id,
                    'section_head_id' => $user->approverSectionHead?->id,
                    'manager_id' => null,
                    'npk' => $request->npk,
                    'jabatan' => $request->jabatan,
                    'join_date' => $request->join_date,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'draft',
                    'current_stage' => 'leader',
                ]);
            });

            $evaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($evaluation, 'Intern evaluation created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Admin' && $roleName !== 'HR Admin') {
                if ($roleName === 'Leader' && $internEvaluation->leader_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access', 403);
                }
                if ($roleName === 'Section Head' && $internEvaluation->section_head_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access', 403);
                }
                if ($roleName === 'Manager' && $internEvaluation->manager_id !== $user->id) {
                    return $this->errorResponse('Unauthorized access', 403);
                }
            }

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function updateScores(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $request->validate([
                'scores' => 'required|array',
                'scores.*.criteria_id' => 'required|exists:evaluation_criteria,id',
                'scores.*.score' => 'required|numeric',
            ]);

            $user = Auth::user();
            $roleName = $user->roleLevel?->name;
            $filledByRole = match ($roleName) {
                'Leader' => 'leader',
                'Section Head' => 'section_head',
                default => null,
            };

            $allowedStage = match ($filledByRole) {
                'leader' => $internEvaluation->leader_id === $user->id && $internEvaluation->current_stage === 'leader',
                'section_head' => $internEvaluation->section_head_id === $user->id && $internEvaluation->current_stage === 'section_head',
                default => false,
            };

            if (!$allowedStage) {
                return $this->errorResponse('Intern evaluation is locked for score updates', 403);
            }

            foreach ($request->scores as $item) {
                InternEvaluationScore::updateOrCreate(
                    [
                        'intern_evaluation_id' => $internEvaluation->id,
                        'criteria_id' => $item['criteria_id'],
                        'filled_by_role' => $filledByRole,
                    ],
                    [
                        'score' => $item['score'],
                        'filled_by_user_id' => $user->id,
                    ]
                );
            }

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation scores updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function updateRecommendation(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $request->validate([
                'recommended_status' => 'nullable|in:continue,not_continue',
                'notes' => 'nullable|string',
            ]);

            InternEvaluationRecommendation::updateOrCreate(
                ['intern_evaluation_id' => $internEvaluation->id],
                [
                    'recommended_status' => $request->recommended_status,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                ]
            );

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation recommendation updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function submit(InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Leader' || $internEvaluation->leader_id !== $user->id) {
                return $this->errorResponse('Unauthorized to submit', 403);
            }

            if ($internEvaluation->current_stage !== 'leader') {
                return $this->errorResponse('Evaluation cannot be submitted from this stage', 422);
            }

            if (empty($internEvaluation->section_head_id)) {
                return $this->errorResponse('You do not have an Approver Section Head assigned.', 422);
            }

            $internEvaluation->load('recommendation');
            if (!$internEvaluation->recommendation || empty($internEvaluation->recommendation->recommended_status)) {
                return $this->errorResponse('Recommendation is required before submitting', 422);
            }

            $internEvaluation->status = 'submitted_to_section_head';
            $internEvaluation->current_stage = 'section_head';
            $internEvaluation->save();

            InternEvaluationApproval::create([
                'intern_evaluation_id' => $internEvaluation->id,
                'role' => 'leader',
                'user_id' => $user->id,
                'action' => 'submit',
                'acted_at' => now(),
            ]);

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation submitted to section head successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function approve(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            $allowed = match ($internEvaluation->current_stage) {
                'section_head' => $roleName === 'Section Head' && $internEvaluation->section_head_id === $user->id,
                'manager' => $roleName === 'Manager' && $internEvaluation->manager_id === $user->id,
                default => false,
            };

            if (!$allowed) {
                return $this->errorResponse('Unauthorized to approve', 403);
            }

            if ($internEvaluation->current_stage === 'section_head') {
                $user->loadMissing('approverManager');

                if (!$user->approverManager) {
                    return $this->errorResponse('You do not have an Approver Manager assigned.', 422);
                }

                $internEvaluation->manager_id = $user->approverManager->id;
                $internEvaluation->status = 'reviewed_by_section_head';
                $internEvaluation->current_stage = 'manager';
            } elseif ($internEvaluation->current_stage === 'manager') {
                $internEvaluation->status = 'approved';
                $internEvaluation->current_stage = 'done';
            }
            $internEvaluation->save();

            InternEvaluationApproval::create([
                'intern_evaluation_id' => $internEvaluation->id,
                'role' => $internEvaluation->current_stage === 'done' ? 'manager' : 'section_head',
                'user_id' => $user->id,
                'action' => 'approve',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation approved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function reject(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $request->validate(['notes' => 'required|string']);

            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            $allowed = match ($internEvaluation->current_stage) {
                'section_head' => $roleName === 'Section Head' && $internEvaluation->section_head_id === $user->id,
                'manager' => $roleName === 'Manager' && $internEvaluation->manager_id === $user->id,
                default => false,
            };

            if (!$allowed) {
                return $this->errorResponse('Unauthorized to reject', 403);
            }

            $rejectedFromStage = $internEvaluation->current_stage;

            $internEvaluation->status = 'rejected';
            $internEvaluation->current_stage = 'leader';
            $internEvaluation->save();

            InternEvaluationApproval::create([
                'intern_evaluation_id' => $internEvaluation->id,
                'role' => $rejectedFromStage === 'manager' ? 'manager' : 'section_head',
                'user_id' => $user->id,
                'action' => 'reject',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function forwardToHrAdmin(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Section Head' || $internEvaluation->section_head_id !== $user->id) {
                return $this->errorResponse('Unauthorized to forward this evaluation', 403);
            }

            if ($internEvaluation->status !== 'approved' || $internEvaluation->current_stage !== 'done') {
                return $this->errorResponse('Evaluation must be fully approved before forwarding to HR Admin', 422);
            }

            $internEvaluation->status = 'forwarded_to_hr_admin';
            $internEvaluation->current_stage = 'hr_admin';
            $internEvaluation->save();

            InternEvaluationApproval::create([
                'intern_evaluation_id' => $internEvaluation->id,
                'role' => 'section_head',
                'user_id' => $user->id,
                'action' => 'forward_to_hr_admin',
                'notes' => $request->input('notes'),
                'acted_at' => now(),
            ]);

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern evaluation forwarded to HR Admin successfully');
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

            $evaluations = InternEvaluation::with(self::FULL_RELATIONS)
                ->where('current_stage', 'hr_admin')
                ->where('status', 'forwarded_to_hr_admin')
                ->orderBy('updated_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return $this->successResponse($evaluations, 'Pending HR Admin decisions retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * HR Admin memutuskan intern LANJUT — pindahkan ke tabel employees
     * sebagai Contract atau Permanent (sesuai pilihan HRD).
     */
    public function promoteToEmployee(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if (!in_array($roleName, ['Admin', 'HR Admin'])) {
                return $this->errorResponse('Unauthorized', 403);
            }

            if ($internEvaluation->current_stage !== 'hr_admin') {
                return $this->errorResponse('Evaluation is not pending HR Admin decision', 422);
            }

            $request->validate([
                'employment_type' => 'required|in:contract,permanent',
                'end_contract' => 'nullable|date|required_if:employment_type,contract',
                'notes' => 'nullable|string',
            ]);

            $intern = Intern::findOrFail($internEvaluation->intern_id);

            $employee = DB::transaction(function () use ($request, $internEvaluation, $intern, $user) {
                $employee = Employee::create([
                    'npk' => $intern->npk,
                    'name' => $intern->name,
                    'gender' => $intern->gender,
                    'department_id' => $intern->department_id,
                    'section_id' => $intern->section_id,
                    'jabatan' => $intern->jabatan,
                    'employment_type' => $request->employment_type,
                    'join_date' => $intern->join_date,
                    'start_contract' => now(),
                    'end_contract' => $request->employment_type === 'contract' ? $request->end_contract : null,
                    'area_id' => $intern->area_id,
                    'line_id' => $intern->line_id,
                    'station_id' => $intern->station_id,
                    'no_req' => $intern->no_req,
                    'source_intern_id' => $intern->id,
                    'is_active' => true,
                ]);

                $intern->update(['promotion_status' => 'promoted']);

                $internEvaluation->update([
                    'status' => 'completed_promoted',
                    'current_stage' => 'completed',
                ]);

                InternEvaluationApproval::create([
                    'intern_evaluation_id' => $internEvaluation->id,
                    'role' => 'hr_admin',
                    'user_id' => $user->id,
                    'action' => $request->employment_type === 'permanent' ? 'promote_permanent' : 'promote_contract',
                    'notes' => $request->notes,
                    'acted_at' => now(),
                ]);

                return $employee;
            });

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse([
                'intern_evaluation' => $internEvaluation,
                'employee' => $employee,
            ], "Intern successfully promoted to {$request->employment_type} employee");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * HR Admin memutuskan intern TIDAK dilanjutkan.
     */
    public function notExtend(Request $request, InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if (!in_array($roleName, ['Admin', 'HR Admin'])) {
                return $this->errorResponse('Unauthorized', 403);
            }

            if ($internEvaluation->current_stage !== 'hr_admin') {
                return $this->errorResponse('Evaluation is not pending HR Admin decision', 422);
            }

            $request->validate(['notes' => 'nullable|string']);

            $intern = Intern::findOrFail($internEvaluation->intern_id);

            DB::transaction(function () use ($request, $internEvaluation, $intern, $user) {
                $intern->update(['promotion_status' => 'not_extended']);

                $internEvaluation->update([
                    'status' => 'completed_not_extended',
                    'current_stage' => 'completed',
                ]);

                InternEvaluationApproval::create([
                    'intern_evaluation_id' => $internEvaluation->id,
                    'role' => 'hr_admin',
                    'user_id' => $user->id,
                    'action' => 'not_extend_intern',
                    'notes' => $request->notes,
                    'acted_at' => now(),
                ]);
            });

            $internEvaluation->load(self::FULL_RELATIONS);

            return $this->successResponse($internEvaluation, 'Intern contract closed (not extended)');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Trigger H-30 untuk Leader — mirip pendingTriggers() di EvaluationController,
     * versi khusus Intern.
     */
    public function pendingTriggers(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if (!in_array($roleName, ['Leader', 'Admin', 'HR Admin'])) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $query = Intern::query()
                ->where('promotion_status', 'active')
                ->whereNotNull('end_contract')
                ->whereBetween('end_contract', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                ->whereDoesntHave('evaluations', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(60));
                });

            if ($roleName === 'Leader') {
                if ($user->area_id) {
                    $query->where('area_id', $user->area_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $interns = $query->orderBy('end_contract')->get([
                'id', 'npk', 'name', 'jabatan', 'department_id', 'section_id',
                'join_date', 'start_contract', 'end_contract', 'group',
            ]);

            return $this->successResponse($interns, 'Pending intern evaluation triggers retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(InternEvaluation $internEvaluation): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleName = $user->roleLevel?->name;

            if ($roleName !== 'Leader' || $internEvaluation->leader_id !== $user->id) {
                return $this->errorResponse('Unauthorized to delete', 403);
            }

            if ($internEvaluation->status !== 'draft') {
                return $this->errorResponse('Only draft evaluations can be deleted', 422);
            }

            $internEvaluation->delete();

            return $this->successResponse(null, 'Intern evaluation deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}