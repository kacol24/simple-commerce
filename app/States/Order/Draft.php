<?php

namespace App\States\Order;

use App\States\OrderState;

class Draft extends OrderState
{
    public function color()
    {
        return 'gray';
    }

    public function canEditAddress(): bool
    {
        return true;
    }

    public function canEditOrder(): bool
    {
        return true;
    }
}
