<?php

namespace App\Pipelines\Order;

use App\Models\Order;
use Closure;

class CalculatePayment
{
    public function handle(Order $order, Closure $next)
    {
        $sumDiscounts = $order->payments->sum('amount');
        $order->paid_total = $sumDiscounts;
        $order->save();

        return $next($order->refresh());
    }
}
