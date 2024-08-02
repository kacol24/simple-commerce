<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvadersXX\FilamentNestedList\Concern\ModelNestedList;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Category extends Model
{
    use ModelNestedList;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'order',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
