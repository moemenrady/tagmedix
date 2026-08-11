<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin', 'verified'])
    ->prefix('users')
    ->name('users.')
    ->group(function () {

        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::post('/', [UserController::class, 'store'])->name('store');

        Route::put('/{user}', [UserController::class, 'update'])->name('update');

        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
