<?php

use App\Http\Controllers\FinishedGoodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/shipments',
        [FinishedGoodController::class, 'indexShipment']
    )->name('shipments.index');
    Route::get('finished-goods', [FinishedGoodController::class, 'index'])->name('finished-goods.index');
    Route::get('finished-goods/{order}', [FinishedGoodController::class, 'show'])->name('finished-goods.show');
    Route::post('finished-goods/{order}/ship', [FinishedGoodController::class, 'shipOrder'])->name('finished-goods.ship');
    Route::resource('finished-goods', FinishedGoodController::class)->except(['index', 'show']);
});
