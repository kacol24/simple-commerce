<?php

namespace App\Models;

use App\Models\Concerns\FormatsMoney;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OrderItem extends Model
{
    use LogsActivity;
    use FormatsMoney;

    protected $fillable = [
        'order_id',
        'purchasable_type',
        'purchasable_id',
        'title',
        'short_description',
        'option',
        'sku',
        'price',
        'cost_price',
        'quantity',
        'sub_total',
        'discount_total',
        'total',
        'notes',
        'sort',
    ];

    protected $casts = [
        'option' => 'array',
    ];

    protected $appends = [
        'title_with_quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable()
    {
        return $this->morphTo();
    }

    public function getSellPriceAttribute()
    {
        return $this->price - $this->discount_total;
    }

    public function getFormattedPriceAttribute()
    {
        return $this->formatMoney($this->price);
    }

    public function getFormattedSubTotalAttribute()
    {
        return $this->formatMoney($this->sub_total);
    }

    public function getFormattedDiscountTotalAttribute()
    {
        return $this->formatMoney($this->discount_total);
    }

    public function getFormattedTotalAttribute()
    {
        return $this->formatMoney($this->total);
    }

    public function getOptionStringAttribute()
    {
        $mapped = array_map(function ($value) {
            return implode(': ', Arr::only($value, ['key', 'value']));
        }, $this->option);

        return implode(', ', $mapped);
    }

    public function getTotalCostPriceAttribute()
    {
        return $this->quantity * ($this->cost_price ?? 0);
    }

    public function getProfitAttribute()
    {
        return $this->sub_total - $this->total_cost_price;
    }

    public function getTitleWithQuantityAttribute()
    {
        return $this->quantity.' x '.$this->title;
    }

    public function hasDiscount()
    {
        return $this->discount_total > 0;
    }
}
