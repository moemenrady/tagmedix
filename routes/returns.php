<?php

use App\Http\Controllers\ReturnItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('returns', ReturnItemController::class);
});