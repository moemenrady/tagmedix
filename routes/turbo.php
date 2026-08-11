<?php

use App\Http\Controllers\TurboOperationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('turbo')->name('turbo.')->group(function () {

    Route::get('/', [TurboOperationsController::class, 'index'])->name('index');
    Route::get('/data', [TurboOperationsController::class, 'data'])->name('data');
    Route::post('/send-order', [TurboOperationsController::class, 'sendOrderToTurbo'])->name('send_order');

    // Courier Gateway API endpoints
    Route::get('/areas/{government}', [TurboOperationsController::class, 'getAreas'])->name('areas');
    Route::post('/delete-order', [TurboOperationsController::class, 'deleteOrder'])->name('delete_order');
    Route::post('/resend-request', [TurboOperationsController::class, 'resendRequest'])->name('resend_request');
    Route::post('/cancel-request', [TurboOperationsController::class, 'cancelRequest'])->name('cancel_request');
});
