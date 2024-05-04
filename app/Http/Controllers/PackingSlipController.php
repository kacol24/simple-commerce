<?php

namespace App\Http\Controllers;

use App\Models\Order;

class PackingSlipController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Order $order)
    {
        $data = [
            'order'      => $order,
            'orderItems' => $order->items,
            'customer'   => $order->customer,
            'reseller'   => $order->reseller,
            'channel'    => $order->channel,
        ];

        return view('order.packing_slip', $data);
    }
}
