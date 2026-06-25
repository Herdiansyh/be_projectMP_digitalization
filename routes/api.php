<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FptkController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});

Route::get('/master-data', [MasterDataController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    // FPTK Routes
    Route::prefix('fptk')->group(function () {
        Route::get('/', [FptkController::class, 'index']);
        Route::get('/pending', [FptkController::class, 'pendingApproval']);
        Route::get('/approval-history', [FptkController::class, 'approvalHistory']);
        Route::get('/approvers', [FptkController::class, 'getApprovers']); 
        Route::post('/', [FptkController::class, 'store']);
        Route::get('/{noReq}', [FptkController::class, 'show']);
        Route::delete('/{noReq}', [FptkController::class, 'destroy']);
    });

    // Approval Routes
    Route::prefix('approvals')->group(function () {
        Route::post('/{noReq}/review', [ApprovalController::class, 'review']);
        Route::get('/{noReq}', [ApprovalController::class, 'showForReview']);
        Route::get('/{noReq}/history', [ApprovalController::class, 'history']);
        
    });
    Route::middleware('admin')->prefix('users')->group(function () {
    Route::get('/',                      [UserController::class, 'index']);
    Route::post('/',                     [UserController::class, 'store']);
    Route::get('/{user}',                [UserController::class, 'show']);
    Route::put('/{user}',                [UserController::class, 'update']);
    Route::delete('/{user}',             [UserController::class, 'destroy']);
    Route::post('/{user}/reset-password',[UserController::class, 'resetPassword']);
    });
});
