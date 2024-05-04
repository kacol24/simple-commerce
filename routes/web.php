<?php

use App\Http\Controllers\PackingSlipController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{order}/packing-slip', PackingSlipController::class)
     ->name('orders.packing_slip');
