<?php

namespace App\Models;

use App\Models\Amountables\Discount;
use App\Models\Amountables\Fee;
use App\Models\Amountables\Payment;
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
        'shipping_total',
        'shipping_breakdown',
        'discount_total',
        'fees_total',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'shipping_breakdown' => 'json',
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

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function amounts()
    {
        return $this->hasMany(OrderAmount::class, 'order_id');
    }

    public function discounts()
    {
        return $this->amounts()->where('amountable_type', Discount::class);
    }

    public function fees()
    {
        return $this->amounts()->where('amountable_type', Fee::class);
    }

    public function payments()
    {
        return $this->amounts()->where('amountable_type', Payment::class);
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

    public function setSubtotal($subtotal)
    {
        $shipping = $this->shipping_total;
        $discount = $this->discount_total;
        $fee = $this->fees_total;
        $grandTotal = $subtotal + $shipping - $discount + $fee;

        $this->sub_total = $subtotal;
        $this->grand_total = $grandTotal;
        $this->save();
    }

    public function setShippingTotal($shipping)
    {
        $subtotal = $this->sub_total;
        $discount = $this->discount_total;
        $fee = $this->fees_total;
        $grandTotal = $subtotal + $shipping - $discount + $fee;

        $this->shipping_total = $shipping;
        $this->grand_total = $grandTotal;
        $this->save();
    }

    public function setDiscountTotal($discount)
    {
        $subtotal = $this->sub_total;
        $shipping = $this->shipping_total;
        $fee = $this->fees_total;
        $grandTotal = $subtotal + $shipping - $discount + $fee;

        $this->discount_total = $discount;
        $this->grand_total = $grandTotal;
        $this->save();
    }

    public function setFeesTotal($fee)
    {
        $subtotal = $this->sub_total;
        $shipping = $this->shipping_total;
        $discount = $this->discount_total;
        $grandTotal = $subtotal + $shipping - $discount + $fee;

        $this->fees_total = $fee;
        $this->grand_total = $grandTotal;
        $this->save();
    }
}
