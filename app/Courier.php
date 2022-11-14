<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'logo',
    	'created_by', 'updated_by', 'deleted_by'
    ];
}
