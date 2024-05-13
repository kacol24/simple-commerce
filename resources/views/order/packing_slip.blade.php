<!doctype html>
<html lang="en" style="width: 48mm;margin:auto;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Packing Slip | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <style>
        @page {
            size: 58mm 200mm;
        }

        table, figure {
            page-break-inside: avoid; /* Prevent the table from breaking across pages */
        }

        * {
            font-family: monospace;
        }
    </style>
</head>
<body style="width: 48mm; margin: auto;">
@foreach($orders as $order)
    <div style="margin-top: 10mm;page-break-inside: avoid !important;">
        @unless($order->isReseller())
            <strong class="text-center d-block" style="margin-bottom: 5mm;">
                {{ $order->channel->name }}
            </strong>
        @endunless
        <table style="width: 100%">
            <tr>
                <th>Penerima</th>
            </tr>
            <tr>
                <td>
                    {{ $order->recipient_name }}
                </td>
            </tr>
            @if($order->recipient_phone)
                <tr>
                    <td>
                        {{ '0' . $order->recipient_phone }}
                    </td>
                </tr>
            @endif
            @if($order->recipient_address)
                <tr>
                    <td>{!! nl2br($order->recipient_address) !!}</td>
                </tr>
            @endif
        </table>
        <table style="margin-top: 5mm; width: 100%;">
            <tr>
                <th>Pengirim</th>
            </tr>
            @if($order->reseller)
                <tr>
                    <td>
                        {{ $order->reseller->name }}
                    </td>
                </tr>
                @if($order->reseller->phone)
                    <tr>
                        <td>
                            {{ '0' . $order->reseller->friendly_phone }}
                        </td>
                    </tr>
                @endif
            @else
                <tr>
                    <td>
                        {{ $order->channel->name }}
                    </td>
                </tr>
                @if($order->channel->url)
                    <tr>
                        <td>
                            {{ $order->channel->url }}
                        </td>
                    </tr>
                @endif
            @endif
        </table>
        <hr style="margin-top: 5mm;border-style: dotted;">
        <strong style="font-size: 6pt;">Packing List</strong>
        <table style="width: 100%;font-size: 6pt;" class="table table-sm table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Qty.</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td class="align-top">
                        {{ $loop->iteration }}
                    </td>
                    <td class="align-top">
                        {{ $item->title }}@if($item->option)
                            <em class="d-block">({{ $item->option_string }})</em>
                        @endif
                    </td>
                    <td class="align-top text-center">
                        {{ $item->quantity }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @unless($order->isReseller())
        @if($order->channel->receipt_footer)
            <div class="text-center d-block" style="margin-top: 5mm;">
                {!! $order->channel->receipt_footer !!}
            </div>
        @endif
        @if($order->channel->socials)
            @foreach($order->channel->socials as $icon => $handle)
                <div class="text-center">
                    <i class="{{ $icon }}"></i> {{ $handle }}
                </div>
            @endforeach
        @endif
    @endunless
    @unless($loop->last)
        <hr style="margin-top: 5mm;border-style: dashed;">
    @endunless
@endforeach
</body>
</html>
