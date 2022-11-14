<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
	use SoftDeletes;

    protected $table = 'cart';

    protected $fillable = [
    	'user_id'
    ];
}
