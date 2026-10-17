<?php

use App\Http\Controllers\UserGuideController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('user-guide', [UserGuideController::class, 'index'])->name('user-guide.index');
});
