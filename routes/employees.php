<?php

use App\Http\Controllers\EmployeeAdvanceController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin', 'verified'])
    ->prefix('employees')
    ->name('employees.')
    ->group(function () {

        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');

        // Advances, Bonuses & Deductions
        Route::post('/{employee}/advances', [EmployeeAdvanceController::class, 'store'])->name('advances.store');
        Route::delete('/advances/{advance}', [EmployeeAdvanceController::class, 'destroy'])->name('advances.destroy');
    });
