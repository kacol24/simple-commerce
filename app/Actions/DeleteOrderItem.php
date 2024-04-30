<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

class DeleteOrderItem extends AbstractAction
{
    public function execute(Model $orderItem)
    {
        $order = $orderItem->order;
        $orderItem->delete();

        app(Pipeline::class)
            ->send($order->refresh())
            ->through(config('commerce.order.pipelines'));

        return $this;
    }
}
