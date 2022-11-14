<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes;

    protected $table = 'cart_item';

    protected $fillable = [
    	'cart_id', 'warehouse_id', 'product_id',
    	'total_item', 'promosi_id'
    ];

    public function product()
    {
      return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
