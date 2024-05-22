{{ $item->quantity }} x {!! $item->title !!}
@if($item->option)
    _{!! $item->option_string !!}_
@endif
{{ $item->formatted_total }}
