<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ManageOrders;
use App\Models\Order;
use App\States\Order\Completed;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ManageOrders::class;
    }

    protected function getStats(): array
    {
        $orderData = Trend::model(Order::class)
                          ->between(
                              start: now()->subYear(),
                              end: now(),
                          )
                          ->perMonth()
                          ->count();

        [$cost, $revenue] = $this->getPageTableQuery()
                                 ->where('status', Completed::class)
                                 ->get()
                                 ->reduceSpread(function ($cost, $revenue, $order) {
                                     $cost += $order->total_cost_price ?? 0;
                                     $revenue += $order->total_before_shipping ?? 0;
                                     
                                     return [$cost, $revenue];
                                 }, 0, 0);

        return [
            Stat::make('Orders', $this->getPageTableQuery()->count())
                ->description($this->getPageTableQuery()->where('status', Completed::class)->count().' completed')
                ->chart(
                    $orderData
                        ->map(fn(TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make(
                'Avg. order value',
                'Rp'.number_format($this->getPageTableQuery()->avg('sub_total'), thousands_separator: '.')
            )
                ->description('Calculated from subtotal'),
            Stat::make(
                'Est. profit',
                'Rp'.number_format($revenue - $cost, thousands_separator: '.')
            )->color($revenue > $cost ? 'success' : 'danger')
                ->description(
                    'Rp'.number_format($revenue, thousands_separator: '.').' (revenue) - Rp.'.number_format($cost,
                        thousands_separator: '.').' (cost)'
                ),
        ];
    }
}
