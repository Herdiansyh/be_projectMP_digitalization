<?php

use App\Http\Controllers\CompetencyCheckpointController;
use App\Http\Controllers\CompetencyMatrixController;
use App\Http\Controllers\EmployeeAssessmentController;
use App\Http\Controllers\PermissionMatrixController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FptkController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CompetencyCategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationCriteriaController;
use App\Http\Controllers\RoleLevelController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('/master-data', [MasterDataController::class, 'index']);
Route::get('/role-levels', [RoleLevelController::class, 'index']); // publik untuk dropdown login
Route::middleware('auth:api')->group(function () {

    // ── FPTK ──────────────────────────────────────────────────────────
    Route::prefix('fptk')->group(function () {
        // List/detail: semua user login boleh akses (scoping "punya siapa" ditangani di controller)
        Route::get('/', [FptkController::class, 'index']);
        Route::get('/approvers', [FptkController::class, 'getApprovers']);

        // PENTING: route statis (tanpa parameter) HARUS didaftarkan sebelum
        // /{noReq}, kalau tidak Laravel akan mencocokkan "/fptk/pending" ke
        // /{noReq} lebih dulu dan noReq akan berisi string "pending" →
        // findOrFail('pending') → 404 "No query results".
        Route::get('/pending', [FptkController::class, 'pendingApproval'])
            ->middleware('permission:fptk.approve');
        Route::get('/approval-history', [FptkController::class, 'approvalHistory'])
            ->middleware('permission:fptk.view_history');

        Route::get('/{noReq}', [FptkController::class, 'show']);

        // Butuh permission spesifik
        Route::post('/', [FptkController::class, 'store'])
            ->middleware('permission:fptk.create');
        Route::delete('/{noReq}', [FptkController::class, 'destroy'])
            ->middleware('permission:fptk.create');
        Route::post('/{noReq}/process-hrd', [FptkController::class, 'processHrd'])
            ->middleware('permission:fptk.process_hrd');
        Route::post('/{noReq}/assign-manpower', [FptkController::class, 'assignManpower'])
            ->middleware('permission:fptk.process_hrd');
        Route::post('/{noReq}/assign-area-line', [FptkController::class, 'assignAreaLine'])
            ->middleware('permission:fptk.assign_area_line');   
        });

    // ── Approvals ─────────────────────────────────────────────────────
    Route::prefix('approvals')->group(function () {
        Route::post('/{noReq}/review', [ApprovalController::class, 'review'])
            ->middleware('permission:fptk.approve');
        Route::get('/{noReq}', [ApprovalController::class, 'showForReview'])
            ->middleware('permission:fptk.approve');
        Route::get('/{noReq}/history', [ApprovalController::class, 'history']);
    });

    // ── Users ─────────────────────────────────────────────────────────
    // Data Master: cukup is_admin (middleware 'admin'), tidak lagi lewat
    // permission matrix — sesuai keputusan bahwa matrix hanya mengatur
    // hak akses alur bisnis, bukan menu administratif.
    Route::get('/users/{user}/approvers', [UserController::class, 'getApproversForUser']);

    // Didaftarkan sebelum /users/{user} agar "section-heads" tidak tertangkap
    // sebagai route-model-binding parameter {user}.
    Route::get('/users/section-heads', [UserController::class, 'listSectionHeads']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('admin');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('admin');
        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('admin');
        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('admin');
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('admin');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->middleware('admin');
    });

    // ── Permission Matrix (kelola hak akses per role) ────────────────
    // Halaman kelola matrix itu sendiri juga Data Master → is_admin.
    Route::middleware('admin')->prefix('permission-matrix')->group(function () {
        Route::get('/', [PermissionMatrixController::class, 'index']);
        Route::put('/', [PermissionMatrixController::class, 'update']);
    });

    // ── Employees ─────────────────────────────────────────────────────
    // Catatan: akses manpower masih pakai flag per-user (can_view_manpower),
    // bukan permission per-role, karena memang didesain begitu sejak awal.
    Route::get('/employees/active-list', [EmployeeController::class, 'activeList']);
    Route::middleware('manpower')->prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });

    // ── Interns ───────────────────────────────────────────────────────
    Route::get('/interns/active-list', [InternController::class, 'activeList']);
    Route::middleware('manpower')->prefix('interns')->group(function () {
        Route::get('/', [InternController::class, 'index']);
        Route::post('/', [InternController::class, 'store']);
        Route::get('/{intern}', [InternController::class, 'show']);
        Route::put('/{intern}', [InternController::class, 'update']);
        Route::delete('/{intern}', [InternController::class, 'destroy']);
    });

    // ── Stations ──────────────────────────────────────────────────────
    Route::get('/stations', [StationController::class, 'index']); // GET boleh diakses semua user login
    Route::get('/stations/{station}', [StationController::class, 'show']);
    Route::middleware('admin')->prefix('stations')->group(function () {
        Route::post('/', [StationController::class, 'store']);
        Route::put('/{station}', [StationController::class, 'update']);
        Route::delete('/{station}', [StationController::class, 'destroy']);
    });

    // ── Areas ─────────────────────────────────────────────────────────
    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/areas/{area}', [AreaController::class, 'show']);
    Route::middleware('admin')->prefix('areas')->group(function () {
        Route::post('/', [AreaController::class, 'store']);
        Route::put('/{area}', [AreaController::class, 'update']);
        Route::delete('/{area}', [AreaController::class, 'destroy']);
    });

    // ── Lines ─────────────────────────────────────────────────────────
    Route::get('/lines', [LineController::class, 'index']);
    Route::get('/lines/{line}', [LineController::class, 'show']);
    Route::middleware('admin')->prefix('lines')->group(function () {
        Route::post('/', [LineController::class, 'store']);
        Route::put('/{line}', [LineController::class, 'update']);
        Route::delete('/{line}', [LineController::class, 'destroy']);
    });

    // ── Competency Matrix (rubrik penilaian) ────────────────────────
    // Mengelola rubrik/matrix adalah tugas Data Master → is_admin.
    Route::prefix('competency-matrices')->group(function () {
        Route::get('/', [CompetencyMatrixController::class, 'index']);
        Route::get('/{id}', [CompetencyMatrixController::class, 'show']);
        Route::post('/', [CompetencyMatrixController::class, 'store'])
            ->middleware('admin');
        Route::put('/{id}', [CompetencyMatrixController::class, 'update'])
            ->middleware('admin');
        Route::delete('/{id}', [CompetencyMatrixController::class, 'destroy'])
            ->middleware('admin');

        // Nested — kategori & checkpoint selalu dalam konteks satu matrix
        Route::post('/{matrixId}/categories', [CompetencyCategoryController::class, 'store'])
            ->middleware('admin');
    });

    Route::prefix('competency-categories')->group(function () {
        Route::put('/{id}', [CompetencyCategoryController::class, 'update'])
            ->middleware('admin');
        Route::delete('/{id}', [CompetencyCategoryController::class, 'destroy'])
            ->middleware('admin');
        Route::post('/{categoryId}/checkpoints', [CompetencyCheckpointController::class, 'store'])
            ->middleware('admin');
    });

    Route::prefix('competency-checkpoints')->group(function () {
        Route::put('/{id}', [CompetencyCheckpointController::class, 'update'])
            ->middleware('admin');
        Route::delete('/{id}', [CompetencyCheckpointController::class, 'destroy'])
            ->middleware('admin');
    });

    // ── Evaluations ───────────────────────────────────────────────────
    Route::prefix('evaluations')->group(function () {
        Route::get('/', [EvaluationController::class, 'index'])
            ->middleware('permission:evaluations.view');
        Route::get('/criteria', [EvaluationCriteriaController::class, 'index']);
        Route::post('/', [EvaluationController::class, 'store'])
            ->middleware('permission:evaluations.view');
        Route::get('/pending-triggers', [EvaluationController::class, 'pendingTriggers'])
            ->middleware('permission:evaluations.view');
        Route::get('/pending-hr-decisions', [EvaluationController::class, 'pendingHrDecisions'])
            ->middleware('permission:evaluations.hr_decisions');
        Route::get('/{evaluation}', [EvaluationController::class, 'show'])
            ->middleware('permission:evaluations.view,evaluations.hr_decisions');
        Route::put('/{evaluation}', [EvaluationController::class, 'update'])
            ->middleware('permission:evaluations.view');
        Route::delete('/{evaluation}', [EvaluationController::class, 'destroy'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/scores', [EvaluationController::class, 'updateScores'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/recommendation', [EvaluationController::class, 'updateRecommendation'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/submit', [EvaluationController::class, 'submit'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/approve', [EvaluationController::class, 'approve'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/reject', [EvaluationController::class, 'reject'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/forward-to-hr-admin', [EvaluationController::class, 'forwardToHrAdmin'])
            ->middleware('permission:evaluations.view');
        Route::post('/{evaluation}/extend-contract', [EvaluationController::class, 'extendContract'])
            ->middleware('permission:evaluations.hr_decisions');
        Route::post('/{evaluation}/close-contract', [EvaluationController::class, 'closeContract'])
            ->middleware('permission:evaluations.hr_decisions');
    });

    // Mengelola kriteria/form evaluasi adalah tugas Data Master → is_admin.
    Route::middleware('admin')->prefix('evaluation-criteria')->group(function () {
        // Bulk Save
        Route::put('/bulk-save', [EvaluationCriteriaController::class, 'bulkSave']);

        // Groups
        Route::post('/groups', [EvaluationCriteriaController::class, 'storeGroup']);
        Route::put('/groups/{id}', [EvaluationCriteriaController::class, 'updateGroup']);
        Route::delete('/groups/{id}', [EvaluationCriteriaController::class, 'destroyGroup']);
        Route::put('/groups/reorder', [EvaluationCriteriaController::class, 'reorderGroups']);

        // Subgroups
        Route::post('/groups/{groupId}/subgroups', [EvaluationCriteriaController::class, 'storeSubgroup']);
        Route::put('/subgroups/{id}', [EvaluationCriteriaController::class, 'updateSubgroup']);
        Route::delete('/subgroups/{id}', [EvaluationCriteriaController::class, 'destroySubgroup']);
        Route::put('/groups/{groupId}/subgroups/reorder', [EvaluationCriteriaController::class, 'reorderSubgroups']);

        // Criteria
        Route::post('/groups/{groupId}/criteria', [EvaluationCriteriaController::class, 'storeCriteria']);
        Route::put('/criteria/{id}', [EvaluationCriteriaController::class, 'updateCriteria']);
        Route::delete('/criteria/{id}', [EvaluationCriteriaController::class, 'destroyCriteria']);
        Route::put('/groups/{groupId}/criteria/reorder', [EvaluationCriteriaController::class, 'reorderCriteria']);

        // Scale Options
        Route::put('/criteria/{criteriaId}/scale-options', [EvaluationCriteriaController::class, 'updateScaleOptions']);
    });

    // ── Employee Assessment (Leader/QA) ─────────────────────────────
    Route::prefix('assessments')->group(function () {
        Route::get('/assessable', [EmployeeAssessmentController::class, 'assessableEmployees'])
            ->middleware('permission:competency.assess');
        Route::get('/matrix', [EmployeeAssessmentController::class, 'matrixForSubject'])
            ->middleware('permission:competency.assess');
        Route::post('/', [EmployeeAssessmentController::class, 'store'])
            ->middleware('permission:competency.assess');
        Route::get('/history', [EmployeeAssessmentController::class, 'history']);
        Route::get('/my-submissions', [EmployeeAssessmentController::class, 'mySubmissions'])
            ->middleware('permission:competency.assess');
        Route::get('/my-reviews', [EmployeeAssessmentController::class, 'myReviews'])
            ->middleware('permission:competency.qa_review');
        Route::get('/qa-queue', [EmployeeAssessmentController::class, 'qaQueue'])
            ->middleware('permission:competency.qa_review');
            Route::get('/monitoring', [EmployeeAssessmentController::class, 'monitoring'])
            ->middleware('permission:competency.monitor');
       Route::get('/station-summary', [EmployeeAssessmentController::class, 'stationSummary']);
            Route::get('/{assessment}', [EmployeeAssessmentController::class, 'showDetail']);
        Route::post('/{assessment}/qa', [EmployeeAssessmentController::class, 'qaStore'])
            ->middleware('permission:competency.qa_review');
            });
});