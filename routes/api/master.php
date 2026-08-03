<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

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