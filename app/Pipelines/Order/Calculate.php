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
        $order->grand_total = $subtotal + $shipping - $discounts + $fees;
        $order->save();

        return $next($order->refresh());
    }
}
