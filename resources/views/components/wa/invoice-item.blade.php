{!! $item->title !!}
@if($item->option)
    _{!! $item->option_string !!}_
@endif
{{ $item->quantity }} x {{ $item->formatted_price }} = @if($item->hasDiscount())~{{ $item->formatted_sub_total }}~ {{ $item->formatted_total }}@endif
