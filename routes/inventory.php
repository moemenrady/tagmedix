<?php

use App\Modules\Inventory\Controllers\InventoryTransactionController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('item-categories', ItemCategoryController::class);
    Route::resource('inventory-transactions', InventoryTransactionController::class);
});
