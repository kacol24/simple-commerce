<?php

namespace App\States\Order;

use App\States\OrderState;

class Shipped extends OrderState
{
    public function color()
    {
        return 'primary';
    }
}
