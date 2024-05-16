<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Collection extends Model
{
    use ModelTree;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'order',
    ];
}
