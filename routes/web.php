<?php

use App\Http\Controllers\ManpowerPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print/fptk/{noReq}', [\App\Http\Controllers\FptkController::class, 'printView'])->name('fptk.print');
Route::get('/print/manpower/{type}/{id}', [ManpowerPrintController::class, 'printSingle']);
Route::post('/print/manpower/bulk', [ManpowerPrintController::class, 'printBulk']);
