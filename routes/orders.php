<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {


    Route::resource('orders', OrderController::class);
    
    Route::get(
        '/{order}/print',
        [OrderController::class, 'printInvoice']
    )->name('orders.print');

    Route::prefix('orders')->group(function () {
        
        Route::patch(
            '/{id}/change-status',
            [OrderController::class, 'changeStatus']
        );

        Route::post(
            '/{order}/restore',
            [OrderController::class, 'restore']
        );

        Route::delete(
            '/{order}/force-delete',
            [OrderController::class, 'forceDelete']
        );

        Route::post(
            '/{order}/add-item',
            [OrderController::class, 'addItem']
        );

        Route::delete(
            '/items/{item}',
            [OrderController::class, 'removeItem']
        );

        Route::post(
            '/{order}/update-payment',
            [OrderController::class, 'updatePayment']
        );

        Route::get(
            '/{order}/invoice',
            [OrderController::class, 'invoice']
        );

        Route::get(
            '/{order}/print',
            [OrderController::class, 'printInvoice']
        );
    });
});
