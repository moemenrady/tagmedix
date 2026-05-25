<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::middleware('auth')->group(function () {
  Route::get('/error', function (Request $request) {
    $error = $request->get('message', 'حدث خطأ غير متوقع');
    return view('error.create', compact('error'));
  })->name('error.create');

  Route::get('/error-system-data', function (Request $request) {
    $error = session('message', 'حدث خطأ غير متوقع'); // يجلب الرسالة من الـ session
    return view('error.admin', compact('error'));
  })->name('admin-error.create');
});

