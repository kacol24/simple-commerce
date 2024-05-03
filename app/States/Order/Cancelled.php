<?php

namespace App\States\Order;

use App\States\OrderState;

class Cancelled extends OrderState
{
    public function color()
    {
        return 'danger';
    }
}
