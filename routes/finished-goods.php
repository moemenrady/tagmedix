<?php

use App\Http\Controllers\FinishedGoodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/shipments',
        [FinishedGoodController::class, 'indexShipment']
    )->name('shipments.index');
    Route::post(
        '/shipments/sync-turbo',
        [FinishedGoodController::class, 'syncTurboShipments']
    )->name('shipments.sync_turbo');
    Route::post(
        '/shipments/{id}/sync',
        [FinishedGoodController::class, 'syncSingleShipment']
    )->name('shipments.sync_single');
    Route::get(
        '/shipments/{id}/debug-turbo',
        [FinishedGoodController::class, 'debugTurboShipment']
    )->name('shipments.debug_turbo');
    Route::post(
        '/shipments/{id}/cancel',
        [FinishedGoodController::class, 'cancelShipment']
    )->name('shipments.cancel');
    Route::get('finished-goods', [FinishedGoodController::class, 'index'])->name('finished-goods.index');
    Route::post('finished-goods/bulk-ship', [FinishedGoodController::class, 'bulkShip'])->name('finished-goods.bulk-ship');
    Route::get('finished-goods/{order}', [FinishedGoodController::class, 'show'])->name('finished-goods.show');
    Route::post('finished-goods/{order}/ship', [FinishedGoodController::class, 'shipOrder'])->name('finished-goods.ship');
    Route::resource('finished-goods', FinishedGoodController::class)->except(['index', 'show']);
});
