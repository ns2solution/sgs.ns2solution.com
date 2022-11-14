<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopProductPoint extends Model
{
    protected $table = 'top_product_point';

    protected $fillable = [
    	'product_point_id', 'active',
    ];
}
