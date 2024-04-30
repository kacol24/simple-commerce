<?php

namespace App\Pipelines\Order;

use App\Models\Order;
use Closure;

class Calculate
{
    public function handle(Order $order, Closure $next)
    {
        $subtotal = $order->sub_total;
        $shipping = $order->shipping_total;
        $discounts = $order->discount_total;
        $fees = $order->fees_total;
        $grandTotal = $subtotal + $shipping - $discounts + $fees;
        if ($grandTotal < 0) {
            $grandTotal = 0;
        }
        $order->grand_total = $grandTotal;
        $order->save();

        return $next($order->refresh());
    }
}
