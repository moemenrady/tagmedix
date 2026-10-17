<?php

use App\Http\Controllers\ReturnItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('returns', ReturnItemController::class);
    Route::post('returns/{id}/qc', [ReturnItemController::class, 'processQc'])->name('returns.qc');
    Route::post('returns/{id}/refund', [ReturnItemController::class, 'processRefund'])->name('returns.refund');
});