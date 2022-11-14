<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
	use SoftDeletes;
	
    protected $table = 'warehouse';

    protected $fillable = [
        'id', 'short', 'name', 'code'
    ];

    public function stock()
    {
      return $this->hasMany(StockProduct::class, 'warehouse_id', 'id');
    }
}
