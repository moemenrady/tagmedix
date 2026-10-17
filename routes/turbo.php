<?php

use App\Http\Controllers\TurboOperationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('turbo')->name('turbo.')->group(function () {

    Route::get('/', function () {
        return redirect()->route('shipments.index');
    })->name('index');
    Route::get('/data', [TurboOperationsController::class, 'data'])->name('data');
    Route::post('/api-status', [\App\Http\Controllers\TurboController::class, 'apiStatus'])->name('api_status');
    Route::post('/send-order', [TurboOperationsController::class, 'sendOrderToTurbo'])->name('send_order');

    // Courier Gateway API endpoints
    Route::get('/areas/{government}', [TurboOperationsController::class, 'getAreas'])->name('areas');
    Route::post('/delete-order', [TurboOperationsController::class, 'deleteOrder'])->name('delete_order');
    Route::post('/resend-request', [TurboOperationsController::class, 'resendRequest'])->name('resend_request');
    Route::post('/cancel-request', [TurboOperationsController::class, 'cancelRequest'])->name('cancel_request');
});

// External Webhook Callbacks (CSRF exempt)
Route::prefix('turbo')->group(function () {
    Route::post('/webhook', [\App\Http\Controllers\Api\TurboWebhookController::class, 'handle'])->name('turbo.webhook');
    Route::post('/v1/update-order', [\App\Http\Controllers\Api\TurboWebhookController::class, 'handle'])->name('turbo.v1_update_order');
});
