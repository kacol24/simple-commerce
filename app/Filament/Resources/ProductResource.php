<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
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
                Forms\Components\Toggle::make('is_active')
                                       ->label('Active?')
                                       ->required()
                                       ->columnSpan([
                                           'md' => 2,
                                       ]),
                Forms\Components\TextInput::make('title')
                                          ->required()
                                          ->maxLength(255)
                                          ->autocapitalize('words')
                                          ->columnSpan([
                                              'md' => 1,
                                          ]),
                Forms\Components\TextInput::make('default_sku')
                                          ->label('SKU')
                                          ->required()
                                          ->columnSpan([
                                              'md' => 1,
                                          ]),
                Forms\Components\TextInput::make('default_price')
                                          ->numeric()
                                          ->prefix('Rp')
                                          ->label('Price')
                                          ->required()
                                          ->columnSpan([
                                              'md' => 1,
                                          ]),
                Forms\Components\TextInput::make('short_description')
                                          ->label('Description')
                                          ->maxLength(255),
                Forms\Components\RichEditor::make('long_description')
                                           ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('default_price')
                                         ->label('Price')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.')
                                         ->sortable(),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('Publish')
                              ->deselectRecordsAfterCompletion()
                              ->requiresConfirmation()
                              ->action(function (Collection $records) {
                                  Product::whereIn('id', $records->pluck('id')->toArray())
                                         ->update([
                                             'is_active' => true,
                                         ]);
                              }),
                    BulkAction::make('Un-publish')
                              ->deselectRecordsAfterCompletion()
                              ->requiresConfirmation()
                              ->action(function (Collection $records) {
                                  Product::whereIn('id', $records->pluck('id')->toArray())
                                         ->update([
                                             'is_active' => false,
                                         ]);
                              }),
                    //Tables\Actions\DeleteBulkAction::make(),
                    //Tables\Actions\ForceDeleteBulkAction::make(),
                    //Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
