<?php

namespace App\States\Order;

use App\States\OrderState;

class Refunded extends OrderState
{
    public function color()
    {
        return 'danger';
    }
}
