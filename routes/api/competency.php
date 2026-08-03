<?php

use App\Http\Controllers\CompetencyCategoryController;
use App\Http\Controllers\CompetencyCheckpointController;
use App\Http\Controllers\CompetencyMatrixController;
use Illuminate\Support\Facades\Route;

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

    // Nested — kategori selalu dalam konteks satu matrix
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