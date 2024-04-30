<?php

namespace App\Pipelines\Order;

use App\Models\Order;
use Closure;

class CalculateDiscounts
{
    public function handle(Order $order, Closure $next)
    {
        $sumDiscounts = $order->discounts->sum('amount');
        $order->discount_total = $sumDiscounts;
        $order->save();

        return $next($order->refresh());
    }
}
