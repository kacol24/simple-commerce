<?php

namespace App\Models\Concerns;

use App\Models\Price;

trait HasPrices
{
    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function basePrices()
    {
        return $this->prices()->whereTier(1)->whereNull('customer_group_id');
    }
}
