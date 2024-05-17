<?php

namespace App\States\Order;

use App\States\OrderState;

class UnderShipment extends OrderState
{
    public function color()
    {
        return 'primary';
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
