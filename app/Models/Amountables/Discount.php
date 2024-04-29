<?php

namespace App\Models\Amountables;

class Discount implements Amountable
{
    public static function operation()
    {
        return AmountableOperation::SUBTRACT;
    }

    public static function target()
    {
        return AmountableTarget::SUBTOTAL;
    }
}
