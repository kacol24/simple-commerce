@foreach($bulkOrders as $customer => $orders)
Invoice pesanan @unless($orders->first()->isReseller())*{{ $orders->first()->channel->name }}*@endunless


*CUSTOMER*
{{ $orders->first()->customer->name }}

@php($shippings = [])
*INVOICE*
@foreach($orders as $order)
@foreach($order->items as $item)
{{ $item->quantity }} x {{ $item->title }}
@if($item->option)
    _{{ $item->option_string }}_
@endif
{{ $item->formatted_total }}

@endforeach
@if($order->hasShipping())
@php($shippings[] = ['label' => $order->recipient_name, 'cost' => $order->formatted_shipping_total])
@endif
@endforeach
@if(count($shippings))
*ONGKIR*
@foreach($shippings as $shipping)
{{ $shipping['label'] }}: {{ $shipping['cost'] }}
@endforeach
@endif

--------------------
*TOTAL: {{ number_format($orders->sum('grand_total'), thousands_separator: '.') }}*
@unless($loop->last)

====================
@endunless
@endforeach

Pembayaran dapat dilakukan lewat Bank Transfer ke rekening Bank BCA
*087 127 3757*
a.n Fernanda E.P


Mohon untuk mengirimkan bukti transfer ke nomor ini agar pesanan bisa kami proses segera
