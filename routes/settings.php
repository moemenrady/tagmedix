<?php

use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('settings', SettingController::class);

    // Turbo Return Amounts Admin Routes
    Route::resource('turbo-return-amounts', \App\Http\Controllers\TurboReturnAmountController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('turbo-return-amounts/api/areas/{govId}', [\App\Http\Controllers\TurboReturnAmountController::class, 'getAreas']);
});