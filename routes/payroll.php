<?php

use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('payroll', PayrollController::class);
});