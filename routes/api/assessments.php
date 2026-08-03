<?php

use App\Http\Controllers\EmployeeAssessmentController;
use Illuminate\Support\Facades\Route;

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