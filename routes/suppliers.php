<?php

use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{supplier}/payments', [SupplierController::class, 'addPayment'])
        ->name('suppliers.payments.store');
});
