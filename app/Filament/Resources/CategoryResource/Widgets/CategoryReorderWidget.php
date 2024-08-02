<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use InvadersXX\FilamentNestedList\Actions\DeleteAction;
use InvadersXX\FilamentNestedList\Actions\EditAction;
use InvadersXX\FilamentNestedList\Widgets\NestedList as BaseWidget;

class CategoryReorderWidget extends BaseWidget
{
    protected static string $model = Category::class;

    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'CategoryReorderWidget';

    protected bool $enableTreeTitle = true;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title'),
        ];
    }

    protected function getTreeActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
