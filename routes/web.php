<?php

use App\Http\Controllers\PackingSlipController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{order}/packing-slip', PackingSlipController::class)
     ->name('orders.packing_slip');
Route::get('/packing-slip', [PackingSlipController::class, 'bulk'])
     ->name('packing_slip.bulk');
Route::get('/whatsapp/orders/{order}/invoice', [WhatsappController::class, 'invoice'])
     ->name('wa.orders.invoice');
Route::get('/whatsapp/orders/invoice', [WhatsappController::class, 'bulkInvoice'])
     ->name('wa.orders.bulk_invoice');
Route::get('/whatsapp/orders/{order}/confirmation', [WhatsappController::class, 'confirmation'])
     ->name('wa.orders.confirmation');
