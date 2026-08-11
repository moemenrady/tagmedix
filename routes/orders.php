<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // 1. مسار جلب المناطق بالـ AJAX (تم ضبط المسار ليكون تحت /orders/get-areas/)
    Route::get('/orders/get-areas/{governmentId}', [OrderController::class, 'getAreasByGovernment'])
        ->name('orders.get-areas');

    // 2. Resource Routes الأساسية للطلبات (index, create, store, show, edit, update, destroy)
    Route::resource('orders', OrderController::class);

    // 3. المسارات الإضافية المخصصة للطلبات
    Route::prefix('orders')->name('orders.')->group(function () {

        Route::patch('/{id}/change-status', [OrderController::class, 'changeStatus'])->name('change-status');
        Route::get('/{order}/print', [OrderController::class, 'printInvoice'])->name('print');
        Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');

        Route::post('/{order}/restore', [OrderController::class, 'restore'])->name('restore');
        Route::delete('/{order}/force-delete', [OrderController::class, 'forceDelete'])->name('force-delete');

        Route::post('/{order}/add-item', [OrderController::class, 'addItem'])->name('add-item');
        Route::delete('/items/{item}', [OrderController::class, 'removeItem'])->name('remove-item');

        Route::post('/{order}/update-payment', [OrderController::class, 'updatePayment'])->name('update-payment');
    });
});