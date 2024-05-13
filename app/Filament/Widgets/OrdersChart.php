<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Processing;
use App\States\Order\Refunded;
use App\States\Order\Shipped;
use App\States\Order\UnderShipment;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders';

    protected function getData(): array
    {
        $orderData = Trend::model(Order::class)
                          ->between(
                              start: now()->subYear(),
                              end: now(),
                          )
                          ->perMonth()
                          ->count();

        $completed = Trend::query(Order::where('status', Completed::class))
                          ->between(
                              start: now()->subYear(),
                              end: now(),
                          )
                          ->perMonth()
                          ->count();

        $processing = Trend::query(Order::whereIn('status', [
            Processing::class,
            UnderShipment::class,
            Shipped::class,
        ]))
                           ->between(
                               start: now()->subYear(),
                               end: now(),
                           )
                           ->perMonth()
                           ->count();

        $failed = Trend::query(Order::whereIn('status', [Refunded::class, Cancelled::class]))
                       ->between(
                           start: now()->subYear(),
                           end: now(),
                       )
                       ->perMonth()
                       ->count();

        return [
            'datasets' => [
                [
                    'label'           => 'Created',
                    'data'            => $orderData->pluck('aggregate'),
                    'backgroundColor' => 'transparent',
                ],
                [
                    'label'           => 'Processing',
                    'data'            => $processing->pluck('aggregate'),
                    'borderColor'     => '#4ade80',
                    'backgroundColor' => 'transparent',
                ],
                [
                    'label'           => 'Completed',
                    'data'            => $completed->pluck('aggregate'),
                    'borderColor'     => (new Completed(Order::class))->colorHex(),
                    'backgroundColor' => (new Completed(Order::class))->colorHex(),
                ],
                [
                    'label'           => 'Refunded/Cancelled',
                    'data'            => $failed->pluck('aggregate'),
                    'borderColor'     => '#f87171',
                    'backgroundColor' => 'transparent',
                ],
            ],
            'labels'   => $orderData->pluck('date')
                                    ->map(fn($date) => Carbon::parse($date)->format('M y')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
