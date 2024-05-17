<?php

namespace App\States\Order;

use App\States\OrderState;

class Refunded extends OrderState
{
    public function color()
    {
        return 'danger';
    }

    public function canEditAddress(): bool
    {
        return false;
    }

    public function canEditOrder(): bool
    {
        return false;
    }
}
