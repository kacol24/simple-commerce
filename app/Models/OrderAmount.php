<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class OrderAmount extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'amountable_type',
        'name',
        'amount',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    public function getFormattedAmountAttribute()
    {
        return $this->formatMoney($this->amount);
    }

    private function formatMoney($value)
    {
        return 'Rp'.$this->numberFormat($value);
    }

    private function numberFormat($value)
    {
        return number_format($value, 0, ',', '.');
    }
}
