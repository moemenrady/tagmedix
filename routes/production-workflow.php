<?php

use App\Http\Controllers\ProductionWorkflowController;
use Illuminate\Support\Facades\Route;

Route::prefix('production/workflow')->name('production.workflow.')->middleware(['auth'])->group(function () {
    Route::get('/', [ProductionWorkflowController::class, 'index'])->name('index');
    Route::post('/assign', [ProductionWorkflowController::class, 'assignTasks'])->name('assign')->middleware('admin');
    Route::post('/order/{order}/move', [ProductionWorkflowController::class, 'moveStatus'])->name('move');
    Route::get('/order/{order}/history', [ProductionWorkflowController::class, 'getHistory'])->name('history');
    Route::get('/export-not-started', [ProductionWorkflowController::class, 'exportNotStartedOrders'])->name('export-not-started');
});
