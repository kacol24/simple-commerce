<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
                TextInput::make('price')
                         ->label('True Price')
                         ->disabled()
                         ->hidden()
                         ->dehydrated()
                         ->numeric()
                         ->prefix('Rp')
                         ->required()
                         ->columnSpan([
                             'md' => 3,
                         ]),
                Placeholder::make('display_price')
                           ->label('Price')
                           ->content(function (Get $get): string {
                               $price = $get('price') ?? 0;

                               $total = $price;

                               return 'Rp'.number_format($total, 0, ',', '.');
                           })
                           ->columnSpan([
                               'md' => 2,
                           ]),
                TextInput::make('discount')
                         ->label('Discount')
                         ->numeric()
                         ->default(0)
                         ->reactive()
                         ->prefix('- Rp')
                         ->columnSpan([
                             'md' => 3,
                         ]),
                Placeholder::make('discounted_price')
                           ->label('Sell Price')
                           ->content(function (Get $get): string {
                               $price = $get('price') ?? 0;
                               $discount = $get('discount');
                               $total = $price - $discount;

                               return 'Rp'.number_format($total, 0, ',', '.');
                           })
                           ->columnSpan([
                               'md' => 2,
                           ]),
                TextInput::make('quantity')
                         ->label('Qty.')
                         ->numeric()
                         ->minValue(1)
                         ->default(1)
                         ->reactive()
                         ->columnSpan([
                             'md' => 2,
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
                               'md' => 3,
                           ]),
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
                Tables\Columns\TextColumn::make('discount_total')
                                         ->label('Discount')
                                         ->prefix('- Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('sell_price')
                                         ->label('Sell Price')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('quantity')
                                         ->label('Qty.'),
                Tables\Columns\TextColumn::make('sub_total')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('total')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                                           ->using(function (array $data, string $model): Model {
                                               $order = $this->getOwnerRecord();
                                               $product = Product::find($data['product_id']);
                                               $subtotal = $product->default_price * $data['quantity'];
                                               $discountTotal = $data['discount'];
                                               $total = $subtotal - ($discountTotal * $data['quantity']);

                                               \DB::beginTransaction();
                                               $orderItem = $order->items()->updateOrCreate([
                                                   'purchasable_type' => ProductVariant::class,
                                                   'purchasable_id'   => $data['product_id'],
                                               ], [
                                                   'title'             => $product->title,
                                                   'short_description' => $product->short_description,
                                                   'sku'               => $product->default_sku,
                                                   'price'             => $product->default_price,
                                                   'quantity'          => $data['quantity'],
                                                   'sub_total'         => $subtotal,
                                                   'discount_total'    => $discountTotal,
                                                   'total'             => $total,
                                               ]);

                                               $order->refresh();
                                               $sumSubtotal = $order->items->sum('total');
                                               $order->setSubtotal($sumSubtotal);
                                               \DB::commit();

                                               return $orderItem;
                                           })
                                           ->after(function (Component $livewire) {
                                               $livewire->dispatch('refreshProducts', fields: [
                                                   'sub_total', 'grand_total',
                                               ]);
                                           }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
