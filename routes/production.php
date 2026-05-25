<?php

use App\Http\Controllers\ProductionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('production', ProductionController::class);
});