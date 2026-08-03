<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\FptkController;
use Illuminate\Support\Facades\Route;

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