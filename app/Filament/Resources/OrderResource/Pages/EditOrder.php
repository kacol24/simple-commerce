<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                                ->headerActions([
                                    //function ($operation) {
                                    //    return Action::make('save_shipping')
                                    //                 ->action(function (
                                    //                     Section $component,
                                    //                     EditRecord $livewire
                                    //                 ) {
                                    //                     $livewire->saveFormComponentOnly($component);
                                    //
                                    //                     Notification::make()
                                    //                                 ->title('Rate limiting saved')
                                    //                                 ->body('The rate limiting settings have been saved successfully.')
                                    //                                 ->success()
                                    //                                 ->send();
                                    //                 })
                                    //                 ->visible($operation === 'edit')
                                    //                 ->after(function (Component $livewire) {
                                    //                     $livewire->dispatch('refreshProducts');
                                    //                 });
                                    //},
                                    //function ($operation) {
                                    //    return Action::make('Remove')
                                    //                 ->requiresConfirmation()
                                    //                 ->color('danger')
                                    //                 ->action(function (
                                    //                     Section $component,
                                    //                     EditRecord $livewire
                                    //                 ) {
                                    //                     $livewire->saveFormComponentOnly($component);
                                    //
                                    //                     Notification::make()
                                    //                                 ->title('Rate limiting saved')
                                    //                                 ->body('The rate limiting settings have been saved successfully.')
                                    //                                 ->success()
                                    //                                 ->send();
                                    //                 })
                                    //                 ->visible(function ($record) use ($operation) {
                                    //                     return $operation === 'edit' && $record->shipping;
                                    //                 })
                                    //                 ->after(function (Component $livewire) {
                                    //                     $livewire->dispatch('refreshProducts');
                                    //                 });
                                    //},
                                ])
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
            TextInput::make('shipping_fee')
                     ->numeric()
                     ->prefix('Rp')
                     ->columnSpan(1),
            DateTimePicker::make('shipping_date')
                          ->columnSpan(1)
                          ->native(false)
                          ->seconds(false)
                          ->format('Y-m-d H:i:s')
                          ->displayFormat('d F Y, H:i')
                          ->weekStartsOnMonday()
                          ->closeOnDateSelection(),
        ];
    }

    public static function getOrderSummarySection()
    {
        return [
            Select::make('status')
                  ->label('Status')
                  ->relationship('channel', 'name'),
            Select::make('channel_id')
                  ->required()
                  ->relationship('channel', 'name'),
            Placeholder::make('created_at')
                       ->label('Placed At')
                       ->content(function ($record) {
                           return $record->created_at;
                       }),
            Placeholder::make('sub_total')
                       ->label('Subtotal')
                       ->content(function ($record) {
                           return 'Rp'.number_format($record->sub_total, 0, ',', '.');
                       }),
            Group::make()
                 ->schema([
                     Placeholder::make('shipping_total')
                                ->label('Shipping Total')
                                ->content(function ($record) {
                                    return 'Rp'.number_format($record->shipping_total, 0, ',', '.');
                                }),
                     Placeholder::make('discount_total')
                                ->label('Discount Total')
                                ->content(function ($record) {
                                    return '- Rp'.number_format($record->discount_total, 0, ',', '.');
                                }),
                     Placeholder::make('fees_total')
                                ->label('Fees Total')
                                ->content(function ($record) {
                                    return 'Rp'.number_format($record->fees_total, 0, ',', '.');
                                }),
                 ])
                 ->columns(3),
            Placeholder::make('grand_total')
                       ->label('Grand Total')
                       ->content(function ($record) {
                           return new HtmlString('<strong>Rp'.number_format($record->grand_total, 0, ',',
                                   '.').'</strong>');
                       }),
        ];
    }

    #[On('refreshProducts')]
    public function refresh($fields): void
    {
        $this->refreshFormData($fields);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
                       ->relationship()
                       ->schema([
                           Select::make('product_id')
                                 ->label('Product')
                                 ->options(Product::query()->pluck('title', 'id'))
                                 ->native(false)
                                 ->preload()
                                 ->required()
                                 ->reactive()
                                 ->afterStateUpdated(
                                     function ($state, Set $set) {
                                         $price = Product::find($state)->default_price ?? 0;

                                         $set('price', $price);
                                     }
                                 )
                                 ->distinct()
                                 ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                 ->columnSpan([
                                     'md' => 5,
                                 ])
                                 ->searchable(),
                           TextInput::make('price')
                                    ->label('Price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                           TextInput::make('discount')
                                    ->label('Discount')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->prefix('Rp')
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                           TextInput::make('quantity')
                                    ->label('Qty.')
                                    ->numeric()
                                    ->default(1)
                                    ->reactive()
                                    ->columnSpan([
                                        'md' => 1,
                                    ])
                                    ->required(),
                           Placeholder::make('total')
                                      ->content(function (Get $get): string {
                                          $price = $get('price') ?? 0;
                                          $discount = $get('discount') ?? 0;
                                          $qty = $get('quantity') ?? 1;

                                          $total = ($price * $qty) - ($discount * $qty);

                                          return 'Rp'.number_format($total, 0, ',', '.');
                                      })
                                      ->columnSpan([
                                          'md' => 2,
                                      ]),
                       ])
                       ->extraItemActions([
                           Action::make('openProduct')
                                 ->tooltip('Open product')
                                 ->icon('heroicon-m-arrow-top-right-on-square')
                                 ->url(function (array $arguments, Repeater $component): ?string {
                                     $itemData = $component->getRawItemState($arguments['item']);

                                     $product = Product::find($itemData['product_id']);

                                     if (! $product) {
                                         return null;
                                     }

                                     return ProductResource::getUrl('edit', ['record' => $product]);
                                 }, shouldOpenInNewTab: true)
                                 ->hidden(fn(
                                     array $arguments,
                                     Repeater $component
                                 ): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
                       ])
                       ->orderColumn('sort')
                       ->defaultItems(1)
                       ->hiddenLabel()
                       ->columns([
                           'md' => 12,
                       ])
                       ->required();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        dd($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        dd($record);

        return $record;
    }
}
