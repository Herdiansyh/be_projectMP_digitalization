<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FptkController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh']);
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
        Route::post('/{noReq}/process-hrd', [FptkController::class, 'processHrd']);
        Route::post('/{noReq}/assign-manpower', [FptkController::class, 'assignManpower']);
        Route::post('/{noReq}/assign-area-line', [FptkController::class, 'assignAreaLine']);
 
    });

    // Approval Routes
    Route::prefix('approvals')->group(function () {
        Route::post('/{noReq}/review', [ApprovalController::class, 'review']);
        Route::get('/{noReq}', [ApprovalController::class, 'showForReview']);
        Route::get('/{noReq}/history', [ApprovalController::class, 'history']);
        
    });
  Route::get('/users/{user}/approvers',      [UserController::class, 'getApproversForUser']);
    
    Route::middleware('admin')->prefix('users')->group(function () {
    Route::get('/',                      [UserController::class, 'index']);
    Route::post('/',                     [UserController::class, 'store']);
    Route::get('/{user}',                [UserController::class, 'show']);
    Route::put('/{user}',                [UserController::class, 'update']);
    Route::delete('/{user}',             [UserController::class, 'destroy']);
    Route::post('/{user}/reset-password',[UserController::class, 'resetPassword']);
    });
    
    Route::get('/employees/active-list', [EmployeeController::class, 'activeList']);
Route::middleware('manpower')->prefix('employees')->group(function () {
    Route::get('/',              [EmployeeController::class, 'index']);
    Route::post('/',             [EmployeeController::class, 'store']);
    Route::get('/{employee}',    [EmployeeController::class, 'show']);
    Route::put('/{employee}',    [EmployeeController::class, 'update']);
    Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
});

Route::get('/interns/active-list', [InternController::class, 'activeList']);
Route::middleware('manpower')->prefix('interns')->group(function () {
    Route::get('/',            [InternController::class, 'index']);
    Route::post('/',           [InternController::class, 'store']);
    Route::get('/{intern}',    [InternController::class, 'show']);
    Route::put('/{intern}',    [InternController::class, 'update']);
    Route::delete('/{intern}', [InternController::class, 'destroy']);
});

Route::middleware('admin')->prefix('stations')->group(function () {
    Route::post('/', [StationController::class, 'store']);
    Route::put('/{station}', [StationController::class, 'update']);
    Route::delete('/{station}', [StationController::class, 'destroy']);
});
Route::get('/stations', [StationController::class, 'index']); // GET boleh diakses semua user login (untuk dropdown Area/Line)
Route::get('/stations/{station}', [StationController::class, 'show']);

Route::get('/areas', [AreaController::class, 'index']);
Route::get('/areas/{area}', [AreaController::class, 'show']);
Route::middleware('admin')->prefix('areas')->group(function () {
    Route::post('/', [AreaController::class, 'store']);
    Route::put('/{area}', [AreaController::class, 'update']);
    Route::delete('/{area}', [AreaController::class, 'destroy']);
});
Route::get('/lines', [LineController::class, 'index']);
Route::get('/lines/{line}', [LineController::class, 'show']);
Route::middleware('admin')->prefix('lines')->group(function () {
    Route::post('/', [LineController::class, 'store']);
    Route::put('/{line}', [LineController::class, 'update']);
    Route::delete('/{line}', [LineController::class, 'destroy']);
});
});
