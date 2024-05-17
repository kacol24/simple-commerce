<?php

namespace App\States\Order;

use App\States\OrderState;

class PartialPayment extends OrderState
{
    public function color()
    {
        return 'warning';
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
