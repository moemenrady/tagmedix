<?php

use App\Http\Controllers\TurboOperationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('turbo')->name('turbo.')->group(function () {

    Route::get('/', [TurboOperationsController::class, 'index'])->name('index');
    Route::get('/data', [TurboOperationsController::class, 'data'])->name('data');
    Route::post('/turbo', [TurboOperationsController::class, 'storeTurbo'])->name('turbo.store');
    Route::post('/turbo/{turbo}/messages', [TurboOperationsController::class, 'sendMessage'])->name('messages.store');
    Route::post('/turbo/{turbo}/rate', [TurboOperationsController::class, 'rateTurbo'])->name('rate');
});
