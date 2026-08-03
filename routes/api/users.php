<?php

use App\Http\Controllers\PermissionMatrixController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Data Master: cukup is_admin (middleware 'admin'), tidak lagi lewat
// permission matrix — sesuai keputusan bahwa matrix hanya mengatur
// hak akses alur bisnis, bukan menu administratif.
Route::get('/users/{user}/approvers', [UserController::class, 'getApproversForUser']);

// Didaftarkan sebelum /users/{user} agar "section-heads" tidak tertangkap
// sebagai route-model-binding parameter {user}.
Route::get('/users/section-heads', [UserController::class, 'listSectionHeads']);

Route::prefix('users')->middleware('admin')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
    Route::post('/{user}/reset-password', [UserController::class, 'resetPassword']);
});

// ── Permission Matrix (kelola hak akses per role) ────────────────
// Halaman kelola matrix itu sendiri juga Data Master → is_admin.
Route::middleware('admin')->prefix('permission-matrix')->group(function () {
    Route::get('/', [PermissionMatrixController::class, 'index']);
    Route::put('/', [PermissionMatrixController::class, 'update']);
});