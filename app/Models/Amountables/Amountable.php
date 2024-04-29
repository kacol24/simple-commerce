<?php

namespace App\Models\Amountables;

/**
 *
 */
interface Amountable
{
    /**
     * @return \App\Models\Amountables\AmountableOperation
     */
    public static function operation();

    /**
     * @return \App\Models\Amountables\AmountableTarget
     */
    public static function target();
}
