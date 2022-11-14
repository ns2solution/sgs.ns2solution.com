<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopProduct extends Model
{
    protected $table = 'top_product';

    protected $fillable = [
    	'product_id', 'active', 'updated_at'
    ];
}
