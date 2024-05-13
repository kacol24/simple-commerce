<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\States\Order\Completed;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;

class OrdersValueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue';

    protected function getData(): array
    {
        $completed = Trend::query(
            Order::where('status', Completed::class)
        )
                          ->between(
                              start: now()->subYear(),
                              end: now(),
                          )
                          ->perMonth()
                          ->sum('grand_total');

        return [
            'datasets' => [
                [
                    'label'           => 'Total (Rp)',
                    'data'            => $completed->pluck('aggregate'),
                    'borderColor'     => (new Completed(Order::class))->colorHex(),
                    'backgroundColor' => (new Completed(Order::class))->colorHex(),
                ],
            ],
            'labels'   => $completed->pluck('date')
                                    ->map(fn($date) => Carbon::parse($date)->format('M y')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
