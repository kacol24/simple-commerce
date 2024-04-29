<?php

namespace App\Models\Amountables;

class Payment implements Amountable
{
    public static function operation()
    {
        return AmountableOperation::ADDITION;
    }

    public static function target()
    {
        return AmountableTarget::PAID_TOTAL;
    }
}
