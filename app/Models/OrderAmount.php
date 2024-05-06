<?php

namespace App\Models;

use App\Models\Concerns\FormatsMoney;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class OrderAmount extends Model
{
    use LogsActivity;
    use FormatsMoney;

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
}
