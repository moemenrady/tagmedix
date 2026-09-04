<?php

use App\Modules\Inventory\Controllers\InventoryTransactionController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Controllers\InventoryDashboardController;
use App\Modules\Inventory\Controllers\InventoryStockController;


Route::middleware(['auth'])->group(function () {
    Route::resource('item-categories', ItemCategoryController::class);
    Route::resource('inventory-transactions', InventoryTransactionController::class);
});
Route::prefix('inventory')
    ->name('inventory.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [InventoryDashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Inventory Items
        |--------------------------------------------------------------------------
        |
        | Create / store inventory item definition.
        |
        */

        Route::get(
            '/items/create',
            [InventoryController::class, 'create']
        )->name('items.create');

        Route::post(
            '/items',
            [InventoryController::class, 'store']
        )->name('items.store');


        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        |
        | Create initial stock / shipment for an inventory item.
        |
        */

        Route::get(
            '/stocks/create',
            [InventoryStockController::class, 'create']
        )->name('stocks.create');

        Route::post(
            '/stocks',
            [InventoryStockController::class, 'store']
        )->name('stocks.store');


        /*
        |--------------------------------------------------------------------------
        | Inventory Transactions
        |--------------------------------------------------------------------------
        |
        | Receipt / Issue / Adjustment / Return / Damage
        |
        */

        Route::post(
            '/transactions',
            [InventoryTransactionController::class, 'store']
        )->name('transactions.store');
    });
