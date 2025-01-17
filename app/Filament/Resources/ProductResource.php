<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductVariant;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Group::make()
                             ->columns(2)
                             ->schema([
                                 Section::make()
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('title')
                                                     ->required()
                                                     ->maxLength(255)
                                                     ->autocapitalize('words')
                                                     ->datalist(Product::pluck('title'))
                                                     ->autocomplete(false)
                                                     ->columnSpan([
                                                         'default' => 'full',
                                                         'md'      => 1,
                                                     ]),
                                            TextInput::make('default_sku')
                                                     ->label('SKU')
                                                     ->required()
                                                     ->unique(
                                                         'product_variants',
                                                         'sku',
                                                         ignorable: function (Product $record = null) {
                                                             return optional($record)->defaultVariant();
                                                         })
                                                     ->autocomplete(false)
                                                     ->datalist(ProductVariant::get()->pluck('sku'))
                                                     ->columnSpan([
                                                         'default' => 'full',
                                                         'md'      => 1,
                                                     ]),
                                            Fieldset::make('Pricing')
                                                    ->schema([
                                                        TextInput::make('default_price')
                                                                 ->numeric()
                                                                 ->prefix('Rp')
                                                                 ->label('Price')
                                                                 ->required()
                                                                 ->columnSpan([
                                                                     'md' => 1,
                                                                 ]),
                                                        TextInput::make('default_cost_price')
                                                                 ->helperText('Customers will not see this price.')
                                                                 ->numeric()
                                                                 ->prefix('Rp')
                                                                 ->label('Cost Price')
                                                                 ->columnSpan([
                                                                     'md' => 1,
                                                                 ]),
                                                    ]),
                                        ]),
                                 Section::make('Additional Info')
                                        ->schema([
                                            TextInput::make('short_description')
                                                     ->label('Description')
                                                     ->maxLength(255)
                                                     ->columnSpanFull(),
                                            RichEditor::make('long_description')
                                                      ->columnSpanFull(),
                                            FileUpload::make('images')
                                                      ->helperText('First image is used as featured or thumbnail.')
                                                      ->image()
                                                      ->multiple()
                                                      ->imageEditor()
                                                      ->moveFiles()
                                                      ->reorderable()
                                                      ->appendFiles()
                                                      ->previewable()
                                                      ->openable()
                                                      ->downloadable()
                                                      ->storeFileNamesIn('original_file_names')
                                                      ->panelLayout('grid'),
                                        ])
                                        ->collapsible()
                                        ->persistCollapsed()
                                        ->collapsed(true),
                             ])
                             ->columnSpan(2),
                        Group::make()
                             ->schema([
                                 Section::make('Status')
                                        ->schema([
                                            Toggle::make('is_active')
                                                  ->label('Active?')
                                                  ->default(true)
                                                  ->required()
                                                  ->columnSpanFull(),
                                        ]),
                                 Section::make('Associations')
                                        ->schema([
                                            Select::make('brand_id')
                                                  ->native(false)
                                                  ->searchable()
                                                  ->relationship('brand', 'name')
                                                  ->preload(),
                                            CheckboxList::make('collections')
                                                        ->searchable()
                                                        ->relationship(titleAttribute: 'title'),
                                            SelectTree::make('categories')
                                                      ->enableBranchNode()
                                                      ->withCount()
                                                      ->independent()
                                                      ->expandSelected()
                                                      ->parentNullValue(-1)
                                                      ->defaultOpenLevel(2)
                                                      ->grouped()
                                                      ->searchable()
                                                      ->relationship('categories', 'title', 'parent_id'),
                                        ]),
                             ])
                             ->columnSpan(1),
                    ])
                    ->columns(3),
            ])
            ->columns([
                'default' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                                          ->toggleable()
                                          ->square()
                                          ->limit(1)
                                          ->limitedRemainingTextSeparate()
                                          ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('title')
                                         ->searchable()
                                         ->sortable()
                                         ->description(function ($record) {
                                             return '['.$record->default_sku.']';
                                         }),
                Tables\Columns\TextColumn::make('brand.name')
                                         ->toggleable(isToggledHiddenByDefault: true)
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('default_price')
                                         ->label('Price')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.')
                                         ->sortable(),
                Tables\Columns\TextColumn::make('default_cost_price')
                                         ->label('Cost Price')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.')
                                         ->sortable()
                                         ->toggleable(),
                Tables\Columns\TextColumn::make('short_description')
                                         ->label('Description')
                                         ->searchable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('is_active')
                                           ->label('Active?'),
                Tables\Columns\TextColumn::make('created_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                                            ->label('Active?'),
                Tables\Filters\SelectFilter::make('brand_id')
                                           ->native(false)
                                           ->preload()
                                           ->label('Brand')
                                           ->relationship('brand', 'name')
                                           ->multiple(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                                         ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('bulk_edit')
                              ->label('Edit')
                              ->deselectRecordsAfterCompletion()
                              ->requiresConfirmation()
                              ->form([
                                  ToggleButtons::make('is_active')
                                               ->label('Active?')
                                               ->inline()
                                               ->boolean(),
                                  Select::make('brand_id')
                                        ->relationship('brand', 'name')
                                        ->preload(),
                              ])
                              ->action(function (array $data, Collection $records) {
                                  $updates = [];
                                  $notifies = [];

                                  if (! is_null($data['is_active'])) {
                                      $updates['is_active'] = $data['is_active'];
                                      $notifies[] = 'Active status';
                                  }
                                  if (! is_null($data['brand_id'])) {
                                      $updates['brand_id'] = $data['brand_id'];
                                      $notifies[] = 'Brand';
                                  }

                                  if (count($updates)) {
                                      Product::whereIn('id', $records->pluck('id'))
                                             ->update($updates);

                                      return Notification::make()
                                                         ->title(implode(', ', $notifies).' updated!')
                                                         ->success()
                                                         ->send();
                                  }

                                  Notification::make()
                                              ->title('No record was updated.')
                                              ->info()
                                              ->send();
                              }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
                     ->withoutGlobalScopes([
                         SoftDeletingScope::class,
                     ]);
    }
}
