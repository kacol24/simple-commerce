Konfirmasi pesanan @unless($order->isReseller())*{!! $order->channel->name !!}*@endunless

*CUSTOMER*
{!! $customer->name !!}
@if($customer->phone)
{{ $customer->phone ? '0' . $customer->friendly_phone : '' }}
@endif
@if($order->notes)
_{!! nl2br($order->notes) !!}_
@endif

*ORDER*
@foreach($order->items as $item)
{{ $item->quantity }} x {!! $item->title !!}
@if($item->option)
    _{!! $item->option_string !!}_
@endif
@if($item->notes)
    _notes: {!! $item->notes !!}_
@endif

@endforeach

Cek kembali detail pesanan kamu ya, pastikan udah benar dan sesuai...
