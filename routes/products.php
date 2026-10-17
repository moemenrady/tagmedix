<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::resource(
    'products',
    ProductController::class
);

Route::prefix('products')->group(function () {

    Route::post(
        '/restore/{id}',
        [ProductController::class, 'restore']
    );

    Route::get(
        '/low-stock/list',
        [ProductController::class, 'lowStock']
    );

    Route::post(
        '/variants/{id}/add-stock',
        [ProductController::class, 'addStock']
    );

    Route::post(
        '/variants/{id}/deduct-stock',
        [ProductController::class, 'deductStock']
    );
});
