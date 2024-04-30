<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Actions\AddOrUpdateOrderItem;
use App\Actions\DeleteOrderItem;
use App\DataObjects\AddOrUpdateOrderItemPayload;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
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
                          'md' => 12,
                      ])
                      ->searchable(),
                Grid::make()
                    ->schema([
                        TextInput::make('price')
                                 ->label('True Price')
                                 ->disabled()
                                 ->hidden()
                                 ->dehydrated()
                                 ->numeric()
                                 ->prefix('Rp')
                                 ->required(),
                        Placeholder::make('display_price')
                                   ->label('Price')
                                   ->content(function (Get $get): string {
                                       $price = $get('price') ?? 0;

                                       $total = $price;

                                       return 'Rp'.number_format($total, 0, ',', '.');
                                   }),
                        TextInput::make('quantity')
                                 ->label('Qty.')
                                 ->numeric()
                                 ->minValue(1)
                                 ->default(1)
                                 ->reactive()
                                 ->required(),
                        Placeholder::make('sub_total')
                                   ->content(function (Get $get): string {
                                       $price = $get('price') ?? 0;
                                       $qty = $get('quantity');
                                       $total = $price * $qty;

                                       return 'Rp'.number_format($total, 0, ',', '.');
                                   }),
                        TextInput::make('discount_total')
                                 ->label('Discount')
                                 ->numeric()
                                 ->minValue(0)
                                 ->default(0)
                                 ->reactive()
                                 ->prefix('- Rp'),
                        Placeholder::make('total')
                                   ->content(function (Get $get): string {
                                       $price = $get('price') ?? 0;
                                       $discount = $get('discount_total') ?? 0;
                                       $qty = $get('quantity') ?? 1;

                                       $total = ($price * $qty) - $discount;

                                       return 'Rp'.number_format($total, 0, ',', '.');
                                   }),
                    ])
                    ->columns(5)
                    ->columnSpan(12),
            ])
            ->columns(12);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                                         ->label('SKU'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('price')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('quantity')
                                         ->label('Qty.'),
                Tables\Columns\TextColumn::make('sub_total')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('discount_total')
                                         ->label('Discount')
                                         ->prefix('- Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('total')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                            ->using(function (array $data, string $model): Model {
                                $order = $this->getOwnerRecord();

                                $payload = AddOrUpdateOrderItemPayload::fromFilamentAction($data);

                                return app()->make(AddOrUpdateOrderItem::class)
                                            ->execute($order, $payload);
                            })
                            ->after(function (Component $livewire) {
                                $livewire->dispatch('refreshOrder', fields: [
                                    'sub_total', 'grand_total',
                                ]);
                            }),
            ])
            ->actions([
                EditAction::make()
                          ->mutateRecordDataUsing(function ($data) {
                              $productVariant = $data['purchasable_type']::find($data['purchasable_id']);
                              $data['product_id'] = $productVariant->product_id;

                              return $data;
                          })
                          ->using(function (array $data, string $model): Model {
                              $order = $this->getOwnerRecord();

                              $payload = AddOrUpdateOrderItemPayload::fromFilamentAction($data);

                              return app()->make(AddOrUpdateOrderItem::class)
                                          ->execute($order, $payload);
                          })
                          ->after(function (Component $livewire) {
                              $livewire->dispatch('refreshOrders', fields: [
                                  'sub_total', 'grand_total',
                              ]);
                          }),
                DeleteAction::make()
                            ->using(function (Model $record) {
                                app()->make(DeleteOrderItem::class)->execute($record);
                            })
                            ->after(function (Component $livewire) {
                                $livewire->dispatch('refreshOrders', fields: [
                                    'sub_total', 'grand_total',
                                ]);
                            }),
            ])
            ->bulkActions([
                //Tables\Actions\BulkActionGroup::make([
                //    Tables\Actions\DeleteBulkAction::make(),
                //]),
            ])
            ->paginated(false);
    }
}
