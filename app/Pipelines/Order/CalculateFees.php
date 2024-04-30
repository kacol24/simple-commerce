<?php

namespace App\Pipelines\Order;

use App\Models\Order;
use Closure;

class CalculateFees
{
    public function handle(Order $order, Closure $next)
    {
        $sumFees = $order->fees->sum('amount');
        $order->fees_total = $sumFees;
        $order->save();

        return $next($order->refresh());
    }
}
