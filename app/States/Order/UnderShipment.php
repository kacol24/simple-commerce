<?php

namespace App\States\Order;

use App\States\OrderState;

class UnderShipment extends OrderState
{
    public function color()
    {
        return 'primary';
    }
}
