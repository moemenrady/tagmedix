<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // مسارات مركز الماليات المخصصة
    Route::get('payments/company-supply/create', [PaymentController::class, 'createCompanySupply'])->name('payments.company-supply.create');
    Route::post('payments/company-supply', [PaymentController::class, 'storeCompanySupply'])->name('payments.company-supply.store');
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');

    // مسار الموارد الافتراضي للمدفوعات
    Route::resource('payments', PaymentController::class);
});
