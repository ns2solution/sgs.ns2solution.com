<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockProduct extends Model
{
    use SoftDeletes;

    protected $table = 'master_stock';

    protected $fillable = [
        'product_id', 'warehouse_id', 'stock',
        'created_by', 'updated_by', 'deleted_by','deleted_at'
    ];

    public function product()
    {
      return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function warehouse()
    {
      return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}
