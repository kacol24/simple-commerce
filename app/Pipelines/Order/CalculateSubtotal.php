<?php

namespace App\Pipelines\Order;

use App\Models\Order;
use Closure;

class CalculateSubtotal
{
    public function handle(Order $order, Closure $next)
    {
        $order->refresh();
        $sumSubtotal = $order->items->sum('total');
        $order->sub_total = $sumSubtotal;
        $order->save();

        return $next($order->refresh());
    }
}
