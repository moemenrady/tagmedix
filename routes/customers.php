<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    // مسار البحث عن العملاء برقم الهاتف
    Route::get('/customers/search', [CustomerController::class, 'searchByPhone'])->name('customers.search');
    // تفاصيل عميل
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->name('customers.show');
});
