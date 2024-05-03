<?php

namespace App\States\Order;

use App\States\OrderState;

class Draft extends OrderState
{
    public function color()
    {
        return 'gray';
    }
}
