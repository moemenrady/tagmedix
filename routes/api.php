<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\TurboController as TurboApiController;
use App\Http\Controllers\Api\TurboWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth'])->group(function () {
    Route::apiResource('categories', ItemCategoryController::class);

    Route::get('items/statistics', [ItemController::class, 'statistics']);
    Route::get('items/low-stock', [ItemController::class, 'lowStock']);
    Route::patch('items/{item}/restore', [ItemController::class, 'restore']);
    Route::patch('items/{item}/toggle-status', [ItemController::class, 'toggleStatus']);
    Route::apiResource('items', ItemController::class);

    Route::post('inventory/opening-balance', [InventoryController::class, 'openingBalance']);
    Route::post('inventory/add', [InventoryController::class, 'addStock']);
    Route::post('inventory/issue', [InventoryController::class, 'issueStock']);
    Route::post('inventory/return', [InventoryController::class, 'returnStock']);
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock']);
    Route::post('inventory/count', [InventoryController::class, 'inventoryCount']);
    Route::post('inventory/rollback/{transactionId}', [InventoryController::class, 'rollbackTransaction']);
    Route::get('inventory/transactions', [InventoryController::class, 'transactions']);
    Route::get('inventory/items/{itemId}/transactions', [InventoryController::class, 'itemTransactions']);
    Route::get('inventory/items/{itemId}/movement-summary', [InventoryController::class, 'movementSummary']);
    Route::get('inventory/value', [InventoryController::class, 'value']);
});

Route::prefix('turbo')->group(function () {
    Route::post('webhook', TurboWebhookController::class);
    Route::post('add-order', [TurboApiController::class, 'addOrder']);
});

Route::prefix('external-api')->group(function () {
    Route::post('add-order', [TurboApiController::class, 'addOrder']);
    Route::post('edit-order', [TurboApiController::class, 'updateOrder']);
    Route::post('get-status', [TurboApiController::class, 'getStatus']);
    Route::post('search-order', [TurboApiController::class, 'search']);
    Route::get('get-government', [TurboApiController::class, 'getGovernments']);
    Route::get('get-area/{government}', [TurboApiController::class, 'getAreas']);
    Route::post('delete-order', [TurboApiController::class, 'deleteOrder']);
    Route::post('resend-request', [TurboApiController::class, 'resend']);
    Route::post('canceled', [TurboApiController::class, 'cancel']);
});
