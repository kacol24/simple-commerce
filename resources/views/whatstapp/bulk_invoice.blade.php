@foreach($bulkOrders as $customer => $orders)
Invoice pesanan @unless($orders->first()->isReseller())*{{ $orders->first()->channel->name }}*@endunless


*CUSTOMER*
{{ $orders->first()->customer->name }}

*INVOICE*
@foreach($orders as $order)
@foreach($order->items as $item)
{{ $item->title }}
@if($item->option)
_{{ $item->option_string }}_
@endif
{{ $item->quantity }} x {{ $item->formatted_price }} = {{ $item->formatted_total }}

@endforeach
--------------------
@if($order->hasShipping())
Ongkir ({{ $order->recipient_name }}): {{ $order->formatted_shipping_total }}
@endif
@endforeach

*TOTAL: {{ number_format($orders->sum('grand_total'), thousands_separator: '.') }}*

@unless($loop->last)
====================
@endunless
@endforeach
