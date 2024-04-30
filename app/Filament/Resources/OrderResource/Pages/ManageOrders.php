<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Draft;
use App\States\Order\Paid;
use App\States\Order\PartialPayment;
use App\States\Order\PendingPayment;
use App\States\Order\Processing;
use App\States\Order\Refunded;
use App\States\Order\Shipped;
use App\States\Order\UnderShipment;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->form(
                static::createActionFormInputs()
            )->using(
                fn(array $data, string $model) => static::createRecord($data, $model)
            )->successRedirectUrl(fn(Model $record): string => route(EditOrder::getRouteName(), [
                'record' => $record,
            ])),
        ];
    }

    public static function createActionFormInputs(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('channel_id')
                      ->required()
                      ->relationship('channel', 'name'),
            ]),
            Grid::make(2)->schema([
                Select::make('customer_id')
                      ->label('Customer')
                      ->required()
                      ->native(false)
                      ->relationship(
                          name: 'customer',
                          titleAttribute: 'name',
                          modifyQueryUsing: fn(Builder $query) => $query->active()
                      )
                      ->searchable(['name', 'phone'])
                      ->getOptionLabelFromRecordUsing(function (Model $customer) {
                          return '['.$customer->phone.'] '.$customer->name;
                      })
                      ->preload(),
                Select::make('reseller_id')
                      ->label('Reseller')
                      ->relationship(
                          name: 'reseller',
                          titleAttribute: 'name',
                      )
                      ->searchable(['name', 'phone'])
                      ->getOptionLabelFromRecordUsing(function (Model $customer) {
                          return '['.$customer->phone.'] '.$customer->name;
                      })
                      ->preload()
                      ->different('customer_id'),
            ]),
            Textarea::make('notes')
                    ->columnSpanFull(),
        ];
    }

    public static function createRecord(array $data, string $model): Model
    {
        $latestOrderNo = Order::generateOrderNo();

        \DB::beginTransaction();
        $order = $model::create([
            'channel_id'     => $data['channel_id'],
            'customer_id'    => $data['customer_id'],
            'reseller_id'    => $data['reseller_id'],
            'notes'          => $data['notes'],
            'order_no'       => $latestOrderNo,
            'sub_total'      => 0,
            'discount_total' => 0,
            'fees_total'     => 0,
            'grand_total'    => 0,
        ]);
        \DB::commit();

        return $order;
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function getTabs(): array
    {
        $orders = Order::get();

        return [
            null                 => Tab::make('All')
                                       ->badge($orders->count())
                                       ->badgeColor('gray'),
            (new Draft(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Draft::class)
                                       )
                                       ->badge($orders->where('status', Draft::class)->count())
                                       ->badgeColor('gray'),
            (new PendingPayment(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', PendingPayment::class)
                                       )
                                       ->badge($orders->where('status', PendingPayment::class)->count())
                                       ->badgeColor('warning'),
            (new PartialPayment(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', PartialPayment::class)
                                       )
                                       ->badge($orders->where('status', PartialPayment::class)->count())
                                       ->badgeColor('warning'),
            (new Paid(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Paid::class)
                                       )
                                       ->badge($orders->where('status', Paid::class)->count())
                                       ->badgeColor('info'),
            (new Processing(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Processing::class)
                                       )
                                       ->badge($orders->where('status', Processing::class)->count())
                                       ->badgeColor('primary'),
            (new UnderShipment(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', UnderShipment::class)
                                       )
                                       ->badge($orders->where('status', UnderShipment::class)->count())
                                       ->badgeColor('primary'),
            (new Shipped(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Shipped::class)
                                       )
                                       ->badge($orders->where('status', Shipped::class)->count())
                                       ->badgeColor('primary'),
            (new Completed(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Completed::class)
                                       )
                                       ->badge($orders->where('status', Completed::class)->count())
                                       ->badgeColor('success'),
            (new Cancelled(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Cancelled::class)
                                       )
                                       ->badge($orders->where('status', Cancelled::class)->count())
                                       ->badgeColor('danger'),
            (new Refunded(Order::class))
                ->friendlyName() => Tab::make()
                                       ->query(
                                           fn($query) => $query->whereState('status', Refunded::class)
                                       )
                                       ->badge($orders->where('status', Refunded::class)->count())
                                       ->badgeColor('danger'),
        ];
    }
}
