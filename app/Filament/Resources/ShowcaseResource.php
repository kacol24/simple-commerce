<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShowcaseResource\Pages;
use App\Filament\Resources\ShowcaseResource\RelationManagers;
use App\Models\Showcase;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShowcaseResource extends Resource
{
    protected static ?string $model = Showcase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make('Attributes')
                           ->schema([
                               TextInput::make('title')
                                        ->required(),
                               RichEditor::make('description'),
                           ]),
                    Section::make('Meta')
                           ->columnSpan(1)
                           ->schema([
                               Toggle::make('is_active')
                                     ->label('Active?')
                                     ->inline(false),
                               DateTimePicker::make('start_at')
                                             ->native(false)
                                             ->seconds(false),
                               DateTimePicker::make('ends_at')
                                             ->native(false)
                                             ->seconds(false)
                                             ->minDate(now()),
                           ])
                           ->grow(false),
                ])
                     ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('status')
                                         ->sortable()
                                         ->boolean(),
                Tables\Columns\TextColumn::make('title')
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('start_at')
                                         ->dateTime()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                                         ->dateTime()
                                         ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                                           ->label('Active?'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index'  => Pages\ListShowcases::route('/'),
            'create' => Pages\CreateShowcase::route('/create'),
            'edit'   => Pages\EditShowcase::route('/{record}/edit'),
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
