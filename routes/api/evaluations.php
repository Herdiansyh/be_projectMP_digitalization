<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationCriteriaController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/hr-decision-history', [EvaluationController::class, 'hrDecisionHistory'])
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
    Route::post('/{evaluation}/extend-intern-contract', [EvaluationController::class, 'extendInternContract'])
        ->middleware('permission:evaluations.hr_decisions');
    Route::post('/{evaluation}/convert-to-permanent', [EvaluationController::class, 'convertToPermanent'])
        ->middleware('permission:evaluations.hr_decisions');
    Route::post('/{evaluation}/close-contract', [EvaluationController::class, 'closeContract'])
        ->middleware('permission:evaluations.hr_decisions');
});

// ── Evaluation Criteria (kelola rubrik form) ─────────────────────
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