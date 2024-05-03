<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Paid;
use App\States\Order\PartialPayment;
use App\States\Order\PendingPayment;
use App\States\Order\Processing;
use App\States\Order\Refunded;
use App\States\Order\Shipped;
use App\States\Order\UnderShipment;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'order_no';

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
                                       ->preload(),
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
                                       ->preload(),
                Forms\Components\Textarea::make('notes')
                                         ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                                         ->formatStateUsing(function ($state) {
                                             return $state->friendlyName();
                                         })
                                         ->badge()
                                         ->color(fn(Model $order, string $state): string => $order->status->color()),
                Tables\Columns\TextColumn::make('order_no')
                                         ->label('Order No')
                                         ->sortable()
                                         ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                                         ->searchable()
                                         ->description(
                                             fn(Order $record): string => '+62 '.$record->customer->friendly_phone
                                         ),
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
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fees_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('grand_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp'),
                Tables\Columns\TextColumn::make('paid_total')
                                         ->numeric(thousandsSeparator: '.')
                                         ->prefix('Rp'),
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
                Tables\Filters\SelectFilter::make('status')
                                           ->options(Order::getStatusDropdown()),
                Filter::make('created_at')
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
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
