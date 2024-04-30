<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                     ->schema([
                         Section::make('Order Details')
                                ->schema(static::getOrderDetailsSection())
                                ->columns(2)
                                ->columnSpan(1),
                         Section::make('Shipping')
                                ->schema(static::getShippingSection())
                                ->columns(3),
                     ])
                     ->columnSpan(['lg' => 2]),
                Group::make()
                     ->schema([
                         Section::make('Order Summary')
                                ->schema(static::getOrderSummarySection()),
                         Section::make('Activity Log')
                                ->collapsible()
                                ->collapsed(),
                     ])
                     ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['shipping_method'] = $data['shipping_breakdown']['shipping_method'];
        $data['shipping_date'] = $data['shipping_breakdown']['shipping_date'];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['shipping_breakdown'] = [
            'shipping_method' => $data['shipping_method'],
            'shipping_total'  => $data['shipping_total'],
            'shipping_date'   => $data['shipping_date'],
        ];

        \DB::beginTransaction();
        $record->update($data);

        $order = app(Pipeline::class)
            ->send($record->refresh())
            ->through(config('commerce.order.pipelines'))
            ->thenReturn(function ($order) {
                return $order;
            });
        \DB::commit();

        return $order;
    }

    public static function getOrderDetailsSection()
    {
        return [
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
                  ->preload()
                  ->columnSpan(2)
                  ->hint(function (Order $order) {
                      return new HtmlString(
                          '<a target="_blank" href="'.$order->customer->whatsapp_url.'">+62 '.$order->customer->phone.'</a>'
                      );
                  })->columnSpan(1),
            Select::make('reseller_id')
                  ->label('Reseller')
                  ->relationship(
                      name: 'reseller',
                      titleAttribute: 'name',
                  )
                  ->searchable(['name', 'phone'])
                  ->preload()
                  ->different('customer_id')
                  ->columnSpan(2)
                  ->hint(function (Order $order) {
                      if (! $order->reseller) {
                          return false;
                      }

                      return new HtmlString(
                          '<a target="_blank" href="'.$order->reseller->whatsapp_url.'">+62 '.$order->reseller->phone.'</a>'
                      );
                  })->columnSpan(1),
            RichEditor::make('notes')
                      ->columnSpan(2),
        ];
    }

    public static function getShippingSection()
    {
        return [
            TextInput::make('shipping_method')
                     ->columnSpan(1),
            TextInput::make('shipping_total')
                     ->numeric()
                     ->prefix('Rp')
                     ->columnSpan(1),
            DateTimePicker::make('shipping_date')
                          ->columnSpan(1)
                          ->native(false)
                          ->seconds(false)
                          ->format('Y-m-d H:i:s')
                          ->displayFormat('d F Y, H:i')
                          ->weekStartsOnMonday(),
        ];
    }

    public static function getOrderSummarySection()
    {
        return [
            Grid::make()
                ->schema([
                    Group::make()
                         ->schema([
                             Select::make('status')
                                   ->label('Status')
                                   ->relationship('channel', 'name'),
                             Select::make('channel_id')
                                   ->required()
                                   ->relationship('channel', 'name'),
                             Placeholder::make('created_at')
                                        ->label('Placed At')
                                        ->content(function ($record) {
                                            return new HtmlString(
                                                '<abbr title="'.$record->created_at.'">'.$record->created_at->diffForHumans().'</abbr>'
                                            );
                                        }),
                         ]),
                    Group::make()
                         ->extraAttributes(['class' => 'text-right'])
                         ->schema([
                             View::make('order.summary'),
                         ]),
                ])
                ->columns(2),
        ];
    }

    #[On('refreshOrders')]
    public function refresh($fields): void
    {
        $order = app(Pipeline::class)
            ->send($this->getRecord()->refresh())
            ->through(config('commerce.order.pipelines'))
            ->thenReturn(function ($order) {
                return $order;
            });
        $order->refresh();

        $this->refreshFormData($fields);
    }
}
