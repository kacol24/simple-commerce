<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\OrderResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Draft;
use App\States\Order\Processing;
use App\States\Order\Refunded;
use App\States\Order\UnderShipment;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Tags\Tag;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('confirmation')
                                    ->label('Order Confirmation')
                                    ->url(fn(Order $record): string => route('wa.orders.confirmation', $record))
                                    ->icon('heroicon-s-check-circle')
                                    ->openUrlInNewTab()
                                    ->color('gray')
                                    ->hidden(function (Model $order) {
                                        return in_array($order->status, [
                                            Completed::class,
                                            Cancelled::class,
                                            Refunded::class,
                                        ]);
                                    }),
            \Filament\Actions\Action::make('packing_slip')
                                    ->label('Packing Slip')
                                    ->url(function (Model $order) {
                                        return route('orders.packing_slip', $order);
                                    })
                                    ->icon('heroicon-s-printer')
                                    ->openUrlInNewTab()
                                    ->color('gray')
                                    ->visible(function (Model $order) {
                                        return in_array($order->status, [
                                            Processing::class,
                                            UnderShipment::class,
                                        ]);
                                    }),
            \Filament\Actions\Action::make('invoice')
                                    ->label('Send Invoice')
                                    ->url(fn(Order $record): string => route('wa.orders.invoice', $record))
                                    ->icon('heroicon-s-currency-dollar')
                                    ->openUrlInNewTab()
                                    ->color('gray')
                                    ->hidden(function (Model $order) {
                                        return in_array($order->status, [
                                            Draft::class,
                                            Completed::class,
                                            Cancelled::class,
                                            Refunded::class,
                                        ]);
                                    }),
            DeleteAction::make()
                        ->visible(function (Model $order) {
                            return in_array($order->status, [Draft::class]);
                        }),
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
                                ->collapsible()
                                ->headerActions([
                                    Action::make('update_status_action')
                                          ->label('Update Status')
                                          ->color('warning')
                                          ->requiresConfirmation()
                                          ->modalContent(function (Order $record) {
                                              return new HtmlString('Current status: '.$record->status->friendlyName());
                                          })
                                          ->action(function (Order $record, array $data) {
                                              if ($record->status->canTransitionTo($data['status'])) {
                                                  $record->status->transitionTo($data['status']);

                                                  Notification::make()
                                                              ->title('Status updated!')
                                                              ->success()
                                                              ->send();
                                              }

                                              return redirect()->route(EditOrder::getRouteName(), $record);
                                          })
                                          ->form([
                                              ToggleButtons::make('status')
                                                           ->label('To status')
                                                           ->inline()
                                                           ->required()
                                                           ->options(Order::getStatusDropdown())
                                                           ->colors(Order::getStatusDropdownColors())
                                                           ->disableOptionWhen(function ($value, Component $livewire) {
                                                               return ! in_array($value,
                                                                   $livewire->getRecord()->status->transitionableStates());
                                                           })
                                                           ->disabled(function (Component $livewire) {
                                                               return in_array(
                                                                   (string) $livewire->getRecord()->status,
                                                                   [Completed::class, Cancelled::class, Refunded::class]
                                                               );
                                                           }),

                                          ]),
                                ]),
                         Section::make('Shipping')
                                ->schema(static::getShippingSection())
                                ->columns([
                                    'default' => 2,
                                ])
                                ->columnSpan(1)
                                ->collapsible()
                                ->headerActions([
                                    Action::make('load_address')
                                          ->color('gray')
                                          ->requiresConfirmation()
                                          ->modalWidth(width: MaxWidth::ExtraLarge)
                                          ->action(function ($data, Order $record, Component $livewire) {
                                              $address = Address::find($data['address']);

                                              $shipping = $record->shipping_breakdown;
                                              $shipping['recipient_name'] = $address->customer->name;
                                              $shipping['recipient_phone'] = $address->customer->phone;
                                              $shipping['recipient_address'] = $address->address;

                                              $record->shipping_breakdown = $shipping;
                                              $record->save();

                                              Notification::make()
                                                          ->title('Recipient loaded!')
                                                          ->success()
                                                          ->send();

                                              return redirect()->route(EditOrder::getRouteName(), $record);
                                          })
                                          ->form([
                                              Select::make('shipping_customer_id')
                                                    ->live()
                                                    ->label('Customer')
                                                    ->native(false)
                                                    ->options(function () {
                                                        $customers = Customer::active()->get();

                                                        return $customers->mapWithKeys(function ($customer) {
                                                            return [$customer->id => $customer->name_with_phone];
                                                        });
                                                    })
                                                    ->searchable(['name', 'phone'])
                                                    ->preload()
                                                    ->dehydrated(false)
                                                    ->afterStateUpdated(function (Component $livewire) {
                                                        $livewire->reset('data.address');
                                                    })
                                                    ->getOptionLabelFromRecordUsing(function (Model $customer) {
                                                        $label = [];
                                                        if ($customer->phone) {
                                                            $label[] = '['.$customer->phone.']';
                                                        }

                                                        $label[] = $customer->name;

                                                        return implode(' ', $label);
                                                    })
                                                    ->columnSpan(1),
                                              Radio::make('address')
                                                   ->required()
                                                   ->options(function (Get $get) {
                                                       $customerId = $get('shipping_customer_id');

                                                       return Address::where('customer_id', $customerId)
                                                                     ->pluck('address', 'id');
                                                   }),
                                          ]),
                                ]),
                     ])
                     ->columns(2)
                     ->columnSpan(['lg' => 3]),
                Group::make()
                     ->schema([
                         Section::make('Order Summary')
                                ->schema(static::getOrderSummarySection())
                                ->columns([
                                    '2xl' => 1,
                                ])
                                ->headerActions([
                                    Action::make('View Log')
                                          ->color('info')
                                          ->icon('heroicon-c-queue-list')
                                          ->modalContent(function (Model $order) {
                                              $activityLog = $order->activity_logs;

                                              return view('order.timeline', compact('activityLog'));
                                          })
                                          ->modalSubmitAction(false)
                                          ->modalCancelAction(false),
                                ]),
                     ])
                     ->columnSpan(['lg' => 1]),
            ])
            ->columns(4);
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['shipping_method'] = optional($data['shipping_breakdown'])['shipping_method'];
        $data['shipping_date'] = optional($data['shipping_breakdown'])['shipping_date'];
        $data['recipient_name'] = optional($data['shipping_breakdown'])['recipient_name'];
        $data['recipient_phone'] = optional($data['shipping_breakdown'])['recipient_phone'];
        $data['recipient_address'] = optional($data['shipping_breakdown'])['recipient_address'];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['shipping_breakdown'] = [
            'shipping_method'   => $data['shipping_method'] ?? null,
            'shipping_total'    => $data['shipping_total'] ?? 0,
            'shipping_date'     => $data['shipping_date'] ?? null,
            'recipient_name'    => $data['recipient_name'] ?? null,
            'recipient_phone'   => $data['recipient_phone'] ?? null,
            'recipient_address' => $data['recipient_address'] ?? null,
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

        return $order->refresh();
    }

    public static function getOrderDetailsSection()
    {
        return [
            Group::make()
                 ->schema([
                     Select::make('channel_id')
                           ->required()
                           ->relationship('channel', 'name')
                           ->selectablePlaceholder(false),
                     TextInput::make('status')
                              ->label('Status')
                              ->formatStateUsing(function (Order $record) {
                                  return $record->status->friendlyName();
                              })
                              ->disabled(),
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
                           ->createOptionForm(CustomerResource::getFormSchema())
                           ->editOptionForm(CustomerResource::getFormSchema())
                           ->getOptionLabelFromRecordUsing(function (Model $customer) {
                               $label = [];
                               if ($customer->phone) {
                                   $label[] = '['.$customer->phone.']';
                               }

                               $label[] = $customer->name;

                               return implode(' ', $label);
                           })
                           ->hint(function (Order $order) {
                               if (! $order->customer->phone) {
                                   return false;
                               }

                               return new HtmlString(
                                   '<a target="_blank" href="'.$order->customer->whatsapp_url.'">+62 '.$order->customer->friendly_phone.'</a>'
                               );
                           })->columnSpan(2),
                     SpatieTagsInput::make('tags')
                                    ->suggestions(Tag::where('type', 'order')->pluck('name', 'id'))
                                    ->type('order')
                                    ->columnSpan(2),
                 ])
                 ->columns(2),
            Textarea::make('notes'),
        ];
    }

    public static function getShippingSection()
    {
        return [
            TextInput::make('shipping_method')
                     ->columnSpan(1)
                     ->datalist(function () {
                         $orders = Order::get();

                         $suggestions = [];
                         foreach ($orders as $order) {
                             $suggestions[] = optional($order->shipping_breakdown)['shipping_method'];
                         }

                         return array_unique($suggestions);
                     }),
            TextInput::make('shipping_total')
                     ->numeric()
                     ->prefix('Rp')
                     ->columnSpan(1),
            TextInput::make('shipping_awb')
                     ->columnSpan(1),
            DateTimePicker::make('shipping_date')
                          ->columnSpan(1)
                          ->native(false)
                          ->seconds(false)
                          ->format('Y-m-d H:i:s')
                          ->displayFormat('d F Y, H:i')
                          ->weekStartsOnMonday(),
            Fieldset::make('Recipient')
                    ->schema([
                        TextInput::make('recipient_name')
                                 ->columnSpan(1),
                        TextInput::make('recipient_phone')
                                 ->columnSpan(1)
                                 ->tel()
                                 ->numeric(),
                        Textarea::make('recipient_address')
                                ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 2,
                    ])
                    ->columnSpanFull(),
        ];
    }

    public static function getOrderSummarySection()
    {
        return [
            Placeholder::make('created_at')
                       ->hint(function ($record) {
                           return $record->created_at->diffForHumans();
                       })
                       ->content(function ($record) {
                           return $record->created_at->toDayDateTimeString();
                       }),
            View::make('order.summary'),
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
