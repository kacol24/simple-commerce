<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'order_no';

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('channel_id')
                                       ->required()
                                       ->relationship('channel', 'name'),
                Forms\Components\Select::make('customer_id')
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
                                           return '['.$customer->friendly_phone.'] '.$customer->name;
                                       })
                                       ->preload()
                                       ->createOptionForm(CustomerResource::getFormSchema()),
                Forms\Components\Select::make('reseller_id')
                                       ->label('Reseller')
                                       ->relationship(
                                           name: 'reseller',
                                           titleAttribute: 'name',
                                       )
                                       ->searchable(['name', 'phone'])
                                       ->getOptionLabelFromRecordUsing(function (Model $customer) {
                                           return '['.$customer->friendly_phone.'] '.$customer->name;
                                       })
                                       ->createOptionForm(CustomerResource::getFormSchema())
                                       ->preload(),
                Forms\Components\Textarea::make('notes')
                                         ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                                         ->label('Order No')
                                         ->sortable()
                                         ->searchable()
                                         ->description(function (Order $order) {
                                             return $order->tagsWithType('order')->pluck('name')->implode(', ');
                                         }),
                Tables\Columns\TextColumn::make('recipient_name')
                                         ->description(function (Order $record) {
                                             if ($record->recipient_phone) {
                                                 return '0'.$record->recipient_phone_for_humans;
                                             }
                                         }),
                Tables\Columns\TextColumn::make('customer.name')
                                         ->searchable()
                                         ->description(
                                             function (Order $order) {
                                                 if (! $order->customer->phone) {
                                                     return null;
                                                 }

                                                 return '0'.$order->customer->friendly_phone;
                                             }
                                         ),
                Tables\Columns\TextColumn::make('items.title_with_quantity')
                                         ->listWithLineBreaks()
                                         ->bulleted()
                                         ->limitList(1)
                                         ->expandableLimitedList()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reseller.name')
                                         ->searchable()
                                         ->description(
                                             fn(Order $record) => '+62 '.optional($record->reseller)->friendly_phone
                                         )
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('channel.name')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sub_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true)
                                         ->summarize([
                                             Sum::make()
                                                ->formatStateUsing(
                                                    function ($state) {
                                                        return 'Rp'.number_format($state, 0, ',', '.');
                                                    }
                                                ),
                                         ]),
                Tables\Columns\TextColumn::make('shipping_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true)
                                         ->summarize([
                                             Sum::make()
                                                ->formatStateUsing(
                                                    function ($state) {
                                                        return 'Rp'.number_format($state, 0, ',', '.');
                                                    }
                                                ),
                                         ]),
                Tables\Columns\TextColumn::make('fees_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('- Rp')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('grand_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->summarize([
                                             Sum::make()
                                                ->formatStateUsing(
                                                    function ($state) {
                                                        return 'Rp'.number_format($state, 0, ',', '.');
                                                    }
                                                ),
                                         ]),
                Tables\Columns\TextColumn::make('paid_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                                         ->formatStateUsing(function ($state) {
                                             return $state->friendlyName();
                                         })
                                         ->badge()
                                         ->color(fn(Model $order, string $state): string => $order->status->color()),
                Tables\Columns\TextColumn::make('created_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tag')
                            ->multiple()
                            ->preload()
                            ->relationship('tags', 'name'),
                SelectFilter::make('customer')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->relationship('customer', 'name'),
                SelectFilter::make('status')
                            ->options(Order::getStatusDropdown()),
                SelectFilter::make('channel')
                            ->relationship('channel', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                Filter::make('created_at')
                      ->columnSpanFull()
                      ->columns(2)
                      ->form([
                          DatePicker::make('created_from')
                                    ->placeholder(
                                        fn($state): string => 'Dec 18, '.now()->subYear()->format('Y')
                                    ),
                          DatePicker::make('created_until')
                                    ->placeholder(
                                        fn($state): string => now()->format('M d, Y')
                                    ),
                      ])
                      ->query(function (Builder $query, array $data): Builder {
                          return $query
                              ->when(
                                  $data['created_from'] ?? null,
                                  fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                              )->when(
                                  $data['created_until'] ?? null,
                                  fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                              );
                      })
                      ->indicateUsing(function (array $data): array {
                          $indicators = [];
                          if ($data['created_from'] ?? null) {
                              $indicators['created_from'] = 'Order from '.Carbon::parse($data['created_from'])
                                                                                ->toFormattedDateString();
                          }
                          if ($data['created_until'] ?? null) {
                              $indicators['created_until'] = 'Order until '.Carbon::parse($data['created_until'])
                                                                                  ->toFormattedDateString();
                          }

                          return $indicators;
                      }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_packing_slip')
                              ->label('Packing Slip')
                              ->icon('heroicon-s-printer')
                              ->deselectRecordsAfterCompletion()
                              ->openUrlInNewTab()
                              ->action(function (array $data, Collection $records) {
                                  return redirect()->route(
                                      'packing_slip.bulk',
                                      [
                                          'order_ids' => $records->pluck('id')->toArray(),
                                      ]
                                  );
                              }),
                    BulkAction::make('bulk_invoice')
                              ->label('Invoice')
                              ->icon('heroicon-s-currency-dollar')
                              ->deselectRecordsAfterCompletion()
                              ->action(function (array $data, Collection $records) {
                                  $orderIds = $records->pluck('id')->toArray();

                                  return redirect()->route(
                                      'wa.orders.bulk_invoice', ['order_ids' => $orderIds]
                                  );
                              })
                              ->openUrlInNewTab(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->filtersLayout(filtersLayout: Tables\Enums\FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::ExtraLarge)
            ->filtersFormColumns(2);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\FeesRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrders::route('/'),
            //'create' => Pages\CreateOrder::route('/create'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
