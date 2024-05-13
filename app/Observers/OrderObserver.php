<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updating(Order $order)
    {
        if ($order->getOriginal('status') != $order->status) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('status-update')
                ->withProperties([
                    'new'      => $order->status,
                    'previous' => $order->getOriginal('status'),
                ])
                ->log('status-update');
        }
    }
}
