<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;

class CategoryTreeWidget extends BaseWidget
{
    protected static string $model = Category::class;

    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'Reorder Category';

    protected bool $enableTreeTitle = true;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title'),
        ];
    }

    // INFOLIST, CAN DELETE
    public function getViewFormSchema(): array
    {
        return [
            TextEntry::make('title'),
        ];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }

    // CUSTOMIZE ACTION OF EACH RECORD, CAN DELETE
    protected function getTreeActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
    // OR OVERRIDE FOLLOWING METHODS
    //protected function hasDeleteAction(): bool
    //{
    //    return true;
    //}
    //protected function hasEditAction(): bool
    //{
    //    return true;
    //}
    //protected function hasViewAction(): bool
    //{
    //    return true;
    //}
}
