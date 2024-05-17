<?php

namespace App\States\Order;

use App\States\OrderState;

class Paid extends OrderState
{
    public function color()
    {
        return 'info';
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
