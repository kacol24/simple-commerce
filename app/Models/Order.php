<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'channel_id',
        'customer_id',
        'reseller_id',
        'order_no',
        'sub_total',
        'discount_total',
        'fees_total',
        'grand_total',
        'notes',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reseller()
    {
        return $this->belongsTo(Customer::class, 'reseller_id');
    }

    public static function generateOrderNo(): string
    {
        $year = date('Y');
        $month = date('m');
        $template = '{year}/{month}/{sequence}';

        $latest = Order::query()
                       ->select(
                           DB::RAW('MAX(order_no) as order_no')
                       )->whereYear('created_at', '=', $year)
                       ->whereMonth('created_at', '=', $month)
                       ->first();

        if (! $latest || ! $latest->order_no) {
            $increment = 1;
        } else {
            $segments = explode('/', $latest->order_no);

            if (count($segments) == 1) {
                $increment = 1;
            } else {
                $increment = end($segments) + 1;
            }
        }

        return str_replace(
            [
                '{year}',
                '{month}',
                '{sequence}',
            ],
            [
                $year,
                $month,
                str_pad($increment, 4, 0, STR_PAD_LEFT),
            ],
            $template
        );
    }
}
