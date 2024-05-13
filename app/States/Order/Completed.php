<?php

namespace App\States\Order;

use App\States\OrderState;

class Completed extends OrderState
{
    public function color()
    {
        return 'success';
    }

    public function colorHex()
    {
        return '#a3e635';
    }
}
