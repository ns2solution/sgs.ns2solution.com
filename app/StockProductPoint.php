<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockProductPoint extends Model
{
    use SoftDeletes;

    protected $table = 'master_stock_product_point';

    protected $fillable = [
        'product_point_id', 'warehouse_id', 'stock',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function product()
    {
      return $this->belongsTo(ProductPoint::class, 'product_point_id', 'id');
    }

    public function warehouse()
    {
      return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}
