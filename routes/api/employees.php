<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InternController;
use Illuminate\Support\Facades\Route;

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