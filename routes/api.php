<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


// ── Publik (tanpa auth) ──────────────────────────────────────────
require __DIR__.'/api/auth.php';
require __DIR__.'/api/public.php';

// ── Membutuhkan authentikasi ─────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    require __DIR__.'/api/fptk.php';
    require __DIR__.'/api/users.php';
    require __DIR__.'/api/employees.php';
    require __DIR__.'/api/master.php';
    require __DIR__.'/api/competency.php';
    require __DIR__.'/api/evaluations.php';
    require __DIR__.'/api/assessments.php';
});