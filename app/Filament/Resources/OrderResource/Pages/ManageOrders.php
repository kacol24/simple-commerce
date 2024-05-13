<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\CustomerResource;
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
                      ->createOptionForm(CustomerResource::getFormSchema())
                      ->editOptionForm(CustomerResource::getFormSchema())
                      ->native(false)
                      ->relationship(
                          name: 'customer',
                          titleAttribute: 'name',
                          modifyQueryUsing: fn(Builder $query) => $query->active()
                      )
                      ->searchable(['name', 'phone'])
                      ->getOptionLabelFromRecordUsing(function (Model $customer) {
                          $label = [];
                          if ($customer->phone) {
                              $label[] = '['.$customer->phone.']';
                          }

                          $label[] = $customer->name;

                          return implode(' ', $label);
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
                          $label = [];
                          if ($customer->phone) {
                              $label[] = '['.$customer->phone.']';
                          }

                          $label[] = $customer->name;

                          return implode(' ', $label);
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

        $states = [
            Draft::class,
            PendingPayment::class,
            PartialPayment::class,
            Paid::class,
            Processing::class,
            UnderShipment::class,
            Shipped::class,
            Completed::class,
            Cancelled::class,
            Refunded::class,
        ];

        $tabs = [
            null => Tab::make('All')
                       ->badge($orders->count())
                       ->badgeColor('gray'),
        ];

        foreach ($states as $state) {
            $instance = new $state(Order::class);
            $tabs[$instance->friendlyName()] = Tab::make()
                                                  ->query(
                                                      fn($query) => $query->whereState('status', $state)
                                                  )
                                                  ->badge($orders->where('status', $state)->count())
                                                  ->badgeColor($instance->color());
        }

        return $tabs;
    }
}
