<?php

use App\Http\Controllers\TurboController;
use Illuminate\Support\Facades\Route;

Route::prefix('turbo')->name('turbo.')->group(function () {

    /**
     * عرض التذاكر
     */
    Route::get('/', [

        TurboController::class,

        'index'

    ])->name('index');


    /**
     * إنشاء تذكرة
     */
    Route::post('/', [

        TurboController::class,

        'store'

    ])->name('store');


    /**
     * إضافة رسالة
     */
    Route::post('/{turbo}/messages', [

        TurboController::class,

        'addMessage'

    ])->name('messages.store');


    /**
     * تقييم التذكرة
     */
    Route::post('/{turbo}/rate', [

        TurboController::class,

        'rate'

    ])->name('rate');
});
