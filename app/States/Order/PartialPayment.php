<?php

namespace App\States\Order;

use App\States\OrderState;

class PartialPayment extends OrderState
{
    public function color()
    {
        return 'warning';
    }
}
