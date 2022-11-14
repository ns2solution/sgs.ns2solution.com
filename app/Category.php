<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
	use SoftDeletes;

    protected $table = 'category';

    protected $fillable = [
        'category_name', 'parent_id',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function parent()
    {
        return $this->belongsTo( self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany( self::class, 'parent_id');
    }
}
