<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        @page {
            size: 58mm 200mm;
        }

        table, figure {
            page-break-inside: avoid; /* Prevent the table from breaking across pages */
        }

        * {
            font-family: monospace;
            font-size: 6pt;
        }
    </style>
</head>
<body style="width: 48mm; margin: auto; padding-top: 5mm;">
<table style="width: 100%;">
    <tr>
        <th>Penerima</th>
    </tr>
    <tr>
        <td>
            {{ $customer->name }}
        </td>
    </tr>
    @if($customer->phone)
        <tr>
            <td>
                {{ '0' . $customer->friendly_phone }}
            </td>
        </tr>
    @endif
    @if($order->notes)
        <tr>
            <td>{!! nl2br($order->notes) !!}</td>
        </tr>
    @endif
</table>
<table style="margin-top: 5mm; width: 100%;">
    <tr>
        <th>Pengirim</th>
    </tr>
    <tr>
        <td>
            {{ $channel->name }}
        </td>
    </tr>
    @if($channel->url)
        <tr>
            <td>
                {{ $channel->url }}
            </td>
        </tr>
    @endif
</table>
<hr style="margin-top: 5mm;border-style: dashed;">
<strong>Packing List</strong>
<table style="width: 100%;" class="table table-sm table-bordered">
    <thead>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Qty.</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orderItems as $item)
        <tr>
            <td class="align-top">
                {{ $loop->iteration }}
            </td>
            <td class="align-top">
                {{ $item->title }}@if($item->option)<em class="d-block">({{ $item->option_string }})</em>@endif
            </td>
            <td class="align-top text-center">
                {{ $item->quantity }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
