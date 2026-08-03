<?php

use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\RoleLevelController;
use Illuminate\Support\Facades\Route;

Route::get('/master-data', [MasterDataController::class, 'index']);

// publik untuk dropdown login
Route::get('/role-levels', [RoleLevelController::class, 'index']);