<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print/fptk/{noReq}', [\App\Http\Controllers\FptkController::class, 'printView'])->name('fptk.print');
