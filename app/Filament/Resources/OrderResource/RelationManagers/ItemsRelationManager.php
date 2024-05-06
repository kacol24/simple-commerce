<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Actions\AddOrUpdateOrderItem;
use App\Actions\UpdateOrderItem;
use App\DataObjects\AddOrUpdateOrderItemPayload;
use App\Filament\Resources\ProductOptionResource;
use App\Models\Product;
use App\Models\ProductOption;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        $productOptions = ProductOption::shared()->get();

        return $form
            ->schema([
                Select::make('product_id')
                      ->label('Product')
                      ->options(Product::query()->active()->pluck('title', 'id'))
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
                      ->searchable()
                      ->getSearchResultsUsing(
                          function ($search) {
                              return Product::query()
                                            ->where('title', 'like', '%'.$search.'%')
                                            ->active()
                                            ->limit(50)
                                            ->pluck('title', 'id')
                                            ->toArray();
                          }
                      )
                      ->distinct(),
                Hidden::make('price')
                      ->label('True Price')
                      ->disabled()
                      ->dehydrated()
                      ->required()
                      ->hidden(),
                Grid::make()
                    ->schema([
                        TableRepeater::make('option')
                                     ->label('Options')
                                     ->schema([
                                         Select::make('key')
                                               ->label('Option')
                                               ->options($productOptions->pluck('name', 'id'))
                                               ->native(false)
                                               ->preload()
                                               ->required()
                                               ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                               ->selectablePlaceholder(false)
                                               ->searchable()
                                               ->createOptionForm(ProductOptionResource::getForm())
                                               ->createOptionUsing(function (array $data) {
                                                   $create = ProductOption::create($data);

                                                   return $create->getKey();
                                               }),
                                         TextInput::make('value')
                                                  ->label('Value')
                                                  ->debounce()
                                                  ->required(),
                                     ])
                                     ->colStyles([
                                         'key'   => 'padding-bottom: 16px; width: 50%',
                                         'value' => 'padding-bottom: 16px; width: 50%',
                                     ])
                                     ->defaultItems(0)
                                     ->reorderable(false)
                                     ->reorderableWithDragAndDrop(false)
                                     ->collapsible()
                                     ->columnSpan(6),
                        Group::make()
                             ->schema([
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
                                            })
                                            ->columnSpan(1),
                                 TextInput::make('discount_total')
                                          ->label('Discount')
                                          ->numeric()
                                          ->minValue(0)
                                          ->default(0)
                                          ->reactive()
                                          ->prefix('- Rp')
                                          ->columnSpan(1),
                                 Placeholder::make('total')
                                            ->content(function (Get $get): string {
                                                $price = $get('price') ?? 0;
                                                $discount = (int) $get('discount_total') ?? 0;
                                                $qty = $get('quantity') ?? 1;

                                                $total = ($price * $qty) - $discount;

                                                return 'Rp'.number_format($total, 0, ',', '.');
                                            })
                                            ->columnSpan([
                                                'default' => 2,
                                                'md'      => 1,
                                            ]),
                             ])
                             ->columns([
                                 'default' => 2,
                             ])
                             ->columnSpan(4),
                    ])
                    ->columnSpanFull()
                    ->columns(10),
                TextInput::make('notes'),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                          ->label('Product')
                          ->formatStateUsing(
                              function ($record) {
                                  return new HtmlString(
                                      '<small class="text-gray-400">['.$record->sku.']</small><br>'.$record->title
                                  );
                              }
                          )
                          ->description(
                              function ($record) {
                                  if (! $record->option) {
                                      return null;
                                  }

                                  return $record->option_string;
                              }
                          )
                          ->searchable(['title', 'sku']),
                TextColumn::make('notes'),
                TextColumn::make('price')
                          ->prefix('Rp')
                          ->numeric(thousandsSeparator: '.'),
                TextColumn::make('quantity')
                          ->label('Qty.')
                          ->prefix('x ')
                          ->summarize([
                              Sum::make(),
                          ]),
                TextColumn::make('sub_total')
                          ->prefix('Rp')
                          ->numeric(thousandsSeparator: '.'),
                TextColumn::make('discount_total')
                          ->label('Discount')
                          ->prefix('Rp')
                          ->numeric(thousandsSeparator: '.'),
                TextColumn::make('total')
                          ->prefix('Rp')
                          ->numeric(thousandsSeparator: '.')
                          ->summarize([
                              Sum::make()
                                 ->formatStateUsing(
                                     function ($state) {
                                         return 'Rp'.number_format($state, 0, ',', '.');
                                     }
                                 ),
                          ]),
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
                                $livewire->dispatch('refreshOrders', fields: [
                                    'sub_total', 'grand_total',
                                ]);
                            })
                            ->createAnother(false),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                              ->mutateRecordDataUsing(function ($data) {
                                  $productVariant = $data['purchasable_type']::find($data['purchasable_id']);
                                  $data['product_id'] = $productVariant->product_id;

                                  if (! is_null($data['option']) && count($data['option'])) {
                                      $mapped = [];
                                      foreach ($data['option'] as $option) {
                                          $mapped[] = [
                                              'key'   => $option['id'],
                                              'value' => $option['value'],
                                          ];
                                      }
                                      $data['option'] = $mapped;
                                  }

                                  return $data;
                              })
                              ->using(function (Model $record, array $data): Model {
                                  $payload = AddOrUpdateOrderItemPayload::fromFilamentAction($data);

                                  return app()->make(UpdateOrderItem::class)
                                              ->execute($record, $payload);
                              })
                              ->after(function (Component $livewire) {
                                  $livewire->dispatch('refreshOrders', fields: [
                                      'sub_total', 'grand_total',
                                  ]);
                              }),
                    DeleteAction::make()
                                ->after(function (Component $livewire) {
                                    $livewire->dispatch('refreshOrders', fields: [
                                        'sub_total', 'grand_total',
                                    ]);
                                }),
                ]),
            ])
            ->bulkActions([
                //Tables\Actions\BulkActionGroup::make([
                //    Tables\Actions\DeleteBulkAction::make(),
                //]),
            ])
            ->recordAction(null)
            ->paginated(false);
    }
}
