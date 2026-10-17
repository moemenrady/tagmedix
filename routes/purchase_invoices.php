<?php

use App\Http\Controllers\PurchaseInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
    Route::post('purchase-invoices/{purchase_invoice}/receive', [PurchaseInvoiceController::class, 'receive'])
        ->name('purchase-invoices.receive');
    Route::post('purchase-invoices/{purchase_invoice}/payments', [PurchaseInvoiceController::class, 'addPayment'])
        ->name('purchase-invoices.payments.store');
});
