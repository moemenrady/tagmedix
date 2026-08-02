<?php

use App\Http\Controllers\OrderClassificationController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\PantTypeController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::prefix('order-classifications')->group(function () {

        Route::get('/', [OrderClassificationController::class, 'index'])
            ->name('order_classifications.index');
        Route::resource('colors', ColorController::class);
        Route::resource('sizes', SizeController::class);
        Route::resource('styles', StyleController::class);
        Route::resource('types', TypeController::class);
        Route::resource('pant_types', PantTypeController::class);
    });
});
