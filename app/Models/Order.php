<?php

namespace App\Models;

use App\Models\Amountables\Discount;
use App\Models\Amountables\Fee;
use App\Models\Amountables\Payment;
use App\Models\Concerns\FormatsMoney;
use App\Models\Concerns\LogsActivity;
use App\States\OrderState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class Order extends Model
{
    use HasStates;
    use SoftDeletes;
    use LogsActivity;
    use FormatsMoney;
    use HasTags;

    const SHIPPING_BREAKDOWN_MAP = [
        'shipping_method' => 'Kurir',
        'shipping_date'   => 'Tgl',
    ];

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
        'status'             => OrderState::class,
    ];

    public static function getStatusDropdown()
    {
        return self::getStatesFor('status')
                   ->mapWithKeys(fn($value, $key) => [$value => (new $value(self::class))->friendlyName()]);
    }

    public static function getStatusDropdownColors()
    {
        return self::getStatesFor('status')
                   ->mapWithKeys(fn($value, $key) => [$value => (new $value(self::class))->color()]);
    }

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

    public function getTotalBeforeShippingAttribute()
    {
        return $this->grand_total - $this->shipping_total;
    }

    public function getTotalCostPriceAttribute()
    {
        return $this->items->sum('total_cost_price');
    }

    public function getFormattedSubTotalAttribute()
    {
        return $this->formatMoney($this->sub_total);
    }

    public function getFormattedShippingTotalAttribute()
    {
        return $this->formatMoney($this->shipping_total);
    }

    public function getFormattedDiscountTotalAttribute()
    {
        return $this->formatMoney($this->discount_total);
    }

    public function getFormattedFeesTotalAttribute()
    {
        return $this->formatMoney($this->fees_total);
    }

    public function getFormattedGrandTotalAttribute()
    {
        return $this->formatMoney($this->grand_total);
    }

    public function getFormattedPaidTotalAttribute()
    {
        return $this->formatMoney($this->paid_total);
    }

    public function getAmountDueAttribute()
    {
        return $this->grand_total - $this->paid_total;
    }

    public function getFormattedAmountDueAttribute()
    {
        return $this->formatMoney($this->amount_due);
    }

    public function getInvoiceLinkAttribute()
    {
        config()->set('livewire.inject_morph_markers', false);

        $append = view('whatstapp.invoice', [
            'customer'   => $this->customer,
            'order'      => $this,
            'orderItems' => $this->items,
        ])->render();

        return "https://wa.me/{$this->customer->whatsapp_phone}?lang=en&text=".urlencode($append);
    }

    public function getConfirmationLinkAttribute()
    {
        config()->set('livewire.inject_morph_markers', false);

        $append = view('whatstapp.order_confirmation', [
            'customer'   => $this->customer,
            'order'      => $this,
            'orderItems' => $this->items,
        ])->render();

        return "https://wa.me/{$this->customer->whatsapp_phone}?lang=en&text=".urlencode($append);
    }

    public function getPackingLinkAttribute()
    {
        return view('order.packing_slip');
    }

    public function getRecipientAddressAttribute()
    {
        return optional($this->shipping_breakdown)['recipient_address'] ?? $this->notes;
    }

    public function getRecipientPhoneAttribute()
    {
        return optional($this->shipping_breakdown)['recipient_phone'];
    }

    public function getRecipientNameAttribute()
    {
        return optional($this->shipping_breakdown)['recipient_name'] ?? $this->customer->name;
    }

    public function getActivityLogsAttribute()
    {
        $log = $this->activities()
                    ->orderBy('created_at', 'desc')
                    ->whereIn('event', ['status-update', 'created'])
                    ->get()
                    ->groupBy(
                        fn($log) => $log->created_at->format('Y-m-d')
                    )
                    ->map(
                        function ($logs) {
                            return [
                                'date'  => $logs->first()->created_at->startOfDay(),
                                'items' => $logs->map(function (
                                    $log
                                ) {
                                    return [
                                        'log' => $log,
                                    ];
                                }),
                            ];
                        }
                    );
        return $log;
    }

    public function hasShipping()
    {
        return $this->shipping_total > 0;
    }

    public function isReseller()
    {
        return (bool) $this->reseller_id;
    }

    public function hasAddress()
    {
        return (bool) optional($this->shipping_breakdown)['recipient_address'];
    }

    public function hasRecipient()
    {
        return (bool) optional($this->shipping_breakdown)['recipient_name'];
    }

    public function hasRecipientPhone()
    {
        return (bool) optional($this->shipping_breakdown)['recipient_phone'];
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
