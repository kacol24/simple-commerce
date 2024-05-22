@extends('layouts.wa_invoice')

@section('content')
@foreach($bulkOrders as $customer => $orders)
Invoice pesanan @unless($orders->first()->isReseller())*{{ $orders->first()->channel->name }}*@endunless


*CUSTOMER*
{{ $orders->first()->customer->name }}

@php($shippings = [])
*INVOICE*
@foreach($orders as $order)
@foreach($order->items as $item)
@if($order->isReseller())
<x-wa.invoice-item-reseller :item="$item"/>
@else
<x-wa.invoice-item :item="$item"/>
@endif


@endforeach
@if($order->hasShipping())
@php($shippings[] = ['label' => $order->recipient_name, 'cost' => $order->formatted_shipping_total])
@endif

@if($order->discount_total)
Diskon:
@foreach($order->discounts as $discount)
    _{{ $discount->name }}: ({{ $discount->formatted_amount }})_
@endforeach
@endif
@if($order->fees_total)
Biaya:
@foreach($order->fees as $fee)
    _{{ $fee->name }}: {{ $fee->formatted_amount }}_
@endforeach
@endif
@endforeach

--------------------
@if(count($shippings))
*ONGKIR*
@foreach($shippings as $shipping)
{{ $shipping['label'] }}: {{ $shipping['cost'] }}
@endforeach
@endif

*TOTAL: {{ number_format($orders->sum('grand_total'), thousands_separator: '.') }}*
@unless($loop->last)

====================
@endunless
@endforeach
@endsection
