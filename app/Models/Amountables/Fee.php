<?php

namespace App\Models\Amountables;

class Fee implements Amountable
{
    public static function operation()
    {
        return AmountableOperation::ADDITION;
    }

    public static function target()
    {
        return AmountableTarget::SUBTOTAL;
    }
}
