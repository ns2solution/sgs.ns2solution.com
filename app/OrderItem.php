<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $table = 'order_item';

    protected $fillable = [
    	'order_id', 'product_id', 'total_item',
    	'total_item_before', 'price', 'total_price',
        'promosi_id'
    ];

    public function product()
    {
      return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order()
    {
      return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
