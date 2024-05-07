<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PackingSlipController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Order $order)
    {
        $orders = [$order];

        return view('order.packing_slip', compact('orders'));
    }

    public function bulk(Request $request)
    {
        $order = Order::whereIn('id', $request->order_ids)->first();
        $orders = [
            $order,
            $order,
            $order,
            $order,
        ];

        return view('order.packing_slip', compact('orders'));
    }
}
