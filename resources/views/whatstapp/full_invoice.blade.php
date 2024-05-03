Invoice pesanan *{{ $order->channel->name }}*

*CUSTOMER*
{{ $customer->name }}
@if($customer->phone)
{{ $customer->phone ? '0' . $customer->friendly_phone : '' }}
@endif
@if($order->notes)
_{!! nl2br($order->notes) !!}_
@endif

*INVOICE* #{{ $order->order_no }}
@foreach($order->items as $item)
{{ $item->title }}
@if($item->option)
> {{ $item->option_string }}
@endif
@if($item->notes)
    _notes: {{ $item->notes }}_
@endif
    {{ $item->quantity }} x {{ $item->formatted_price }} = {{ $item->formatted_sub_total }} @if($item->discount_total)- {{ $item->formatted_discount_total }} = {{ $item->formatted_total }} @endif


@endforeach
====================
@if($order->shipping_total)
Ongkir: {{ $order->formatted_shipping_total }}
@isset($order->shipping_breakdown['shipping_method'])
    Kurir: {{ $order->shipping_breakdown['shipping_method'] }}
@endisset
@isset($order->shipping_breakdown['shipping_date'])
    Dikirim: {{ Carbon\Carbon::parse($order->shipping_breakdown['shipping_date'])->toDayDateTimeString() }}
@endisset
@endif
@if($order->discount_total)
Diskon:
@foreach($order->discounts as $discount)
    {{ $discount->name }}: {{ $discount->formatted_amount }}
@endforeach
@endif
@if($order->fees_total)
Biaya:
@foreach($order->fees as $fee)
    {{ $fee->name }}: {{ $fee->formatted_amount }}
@endforeach
@endif
*TOTAL: {{ $order->formatted_grand_total }}*
@if($order->amount_due != $order->grand_total)
Dibayarkan:
@foreach($order->payments as $payment)
    {{ $payment->name }}: ({{ $payment->formatted_amount }})
@endforeach
Sisa tagihan: {{ $order->formatted_amount_due }}
@endif

Pembayaran dapat dilakukan lewat Bank Transfer ke rekening Bank BCA
*087 127 3757*
a.n Fernanda E.P


Mohon untuk mengirimkan bukti transfer ke nomor ini agar pesanan bisa kami proses segera
