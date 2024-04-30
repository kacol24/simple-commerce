<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $order = Order::first();
    dd($order->status);

    return view('welcome');
});
