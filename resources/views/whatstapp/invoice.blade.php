@extends('layouts.wa_invoice')

@section('content')
Invoice pesanan @unless($order->isReseller())*{!! $order->channel->name !!}*@endunless


*CUSTOMER*
{!! $customer->name !!}

*INVOICE*
@foreach($order->items as $item)
@if($order->isReseller())
<x-wa.invoice-item-reseller :item="$item"/>
@else
<x-wa.invoice-item :item="$item"/>
@endif

@endforeach
--------------------
@if($order->hasShipping())
Ongkir: {{ $order->formatted_shipping_total }}
@isset($order->shipping_breakdown['shipping_method'])
    _Kurir: {!! $order->shipping_breakdown['shipping_method'] !!}_
@endisset
@isset($order->shipping_breakdown['shipping_date'])
    _Dikirim: {{ Carbon\Carbon::parse($order->shipping_breakdown['shipping_date'])->locale('id')->translatedFormat('l, j M Y H:i') }}_
@endisset
@endif
@if($order->discount_total)
Diskon:
@foreach($order->discounts as $discount)
    _{!! $discount->name !!}: ({{ $discount->formatted_amount }})_
@endforeach
@endif
@if($order->fees_total)
Biaya:
@foreach($order->fees as $fee)
    _{!! $fee->name !!}: {{ $fee->formatted_amount }}_
@endforeach
@endif
*TOTAL: {{ $order->formatted_grand_total }}*
@if($order->amount_due != $order->grand_total)

--------------------
@foreach($order->payments as $payment)
{!! $payment->name !!}: ({{ $payment->formatted_amount }})
@endforeach
Sisa tagihan: {{ $order->formatted_amount_due }}
@endif
@endsection
