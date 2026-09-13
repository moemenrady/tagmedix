<?php

use App\Http\Controllers\OrderItemStanderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TurboReturnAmountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::resource('settings', SettingController::class);


    /*
    |--------------------------------------------------------------------------
    | Turbo Return Amounts
    |--------------------------------------------------------------------------
    */

    Route::resource('turbo-return-amounts', TurboReturnAmountController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);

    Route::get(
        'turbo-return-amounts/api/areas/{govId}',
        [TurboReturnAmountController::class, 'getAreas']
    );


    /*
    |--------------------------------------------------------------------------
    | Order Item Standards
    |--------------------------------------------------------------------------
    |
    | URL:
    | /order-item-stander
    |
    | Route names:
    | settings.order-item-stander.*
    |
    */

    Route::resource('order-item-stander', OrderItemStanderController::class)
        ->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ])
        ->names('settings.order-item-stander');

    Route::patch(
        'order-item-stander/{id}/toggle-status',
        [OrderItemStanderController::class, 'toggleStatus']
    )->name('settings.order-item-stander.toggle-status');
});