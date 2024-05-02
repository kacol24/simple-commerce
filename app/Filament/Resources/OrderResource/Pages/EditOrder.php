<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Refunded;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
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
use Livewire\Component;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
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
                                ->columns(1)
                                ->columnSpan(1)
                                ->collapsible(),
                         Section::make('Shipping')
                                ->schema(static::getShippingSection())
                                ->columns([
                                    'default' => 2,
                                    'md'      => 3,
                                ])
                                ->collapsible()
                                ->persistCollapsed(),
                     ])
                     ->columnSpan(['lg' => 2]),
                Group::make()
                     ->schema([
                         Section::make('Order Summary')
                                ->schema(static::getOrderSummarySection())
                                ->columns([
                                    '2xl' => 2,
                                ]),
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
        $data['shipping_method'] = optional($data['shipping_breakdown'])['shipping_method'];
        $data['shipping_date'] = optional($data['shipping_breakdown'])['shipping_date'];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['shipping_breakdown'] = [
            'shipping_method' => $data['shipping_method'] ?? null,
            'shipping_total'  => $data['shipping_total'] ?? 0,
            'shipping_date'   => $data['shipping_date'] ?? null,
        ];

        \DB::beginTransaction();
        $record->update($data);

        if ($record->status->canTransitionTo($data['status'])) {
            $record->status->transitionTo($data['status']);
        }

        $order = app(Pipeline::class)
            ->send($record->refresh())
            ->through(config('commerce.order.pipelines'))
            ->thenReturn(function ($order) {
                return $order;
            });
        \DB::commit();

        return $order->refresh();
    }

    public static function getOrderDetailsSection()
    {
        return [
            Group::make()
                 ->schema([
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
                           ->getOptionLabelFromRecordUsing(function (Model $customer) {
                               return '['.$customer->friendly_phone.'] '.$customer->name;
                           })
                           ->hint(function (Order $order) {
                               return new HtmlString(
                                   '<a target="_blank" href="'.$order->customer->whatsapp_url.'">+62 '.$order->customer->friendly_phone.'</a>'
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
                           ->getOptionLabelFromRecordUsing(function (Model $customer) {
                               return '['.$customer->friendly_phone.'] '.$customer->name;
                           })
                           ->different('customer_id')
                           ->hint(function (Order $order) {
                               if (! $order->reseller) {
                                   return false;
                               }

                               return new HtmlString(
                                   '<a target="_blank" href="'.$order->reseller->whatsapp_url.'">+62 '.$order->reseller->phone.'</a>'
                               );
                           })
                           ->columnSpan(1),
                 ])
                 ->columns(2),
            RichEditor::make('notes'),
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
                          ->columnSpan([
                              'default' => 'full',
                              'md'      => 1,
                          ])
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
            Group::make()
                 ->schema([
                     Select::make('status')
                           ->label('Status')
                           ->native(false)
                           ->selectablePlaceholder(false)
                           ->options(Order::getStatusDropdown())
                           ->disableOptionWhen(function ($value, Component $livewire) {
                               return ! in_array($value, $livewire->getRecord()->status->transitionableStates());
                           })
                           ->disabled(function (Component $livewire) {
                               return in_array(
                                   (string) $livewire->getRecord()->status,
                                   [Completed::class, Cancelled::class, Refunded::class]
                               );
                           }),
                     Select::make('channel_id')
                           ->required()
                           ->relationship('channel', 'name'),
                     Placeholder::make('created_at')
                                ->hint(function ($record) {
                                    return $record->created_at->diffForHumans();
                                })
                                ->content(function ($record) {
                                    return $record->created_at->toDayDateTimeString();
                                }),
                 ]),
            Group::make()
                 ->extraAttributes(['class' => 'text-right'])
                 ->schema([
                     View::make('order.summary'),
                 ]),
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
