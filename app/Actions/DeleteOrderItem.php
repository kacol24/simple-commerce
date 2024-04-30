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

        return $this;
    }
}
