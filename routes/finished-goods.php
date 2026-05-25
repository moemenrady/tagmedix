<?php

use App\Http\Controllers\FinishedGoodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('finished-goods', FinishedGoodController::class);
});