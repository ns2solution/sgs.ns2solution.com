<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromosiItem extends Model
{
  
    use Authorizable, SoftDeletes;

    protected $table = 'promosi_item';

    protected $fillable = [
        'promosi_id', 'stock_id', 'type',
        'value','fix_value', 'created_by',
        'updated_by', 'deleted_by','stock_promosi'
    ];

    public function stock()
    {
      return $this->belongsTo(StockProduct::class, 'stock_id', 'id');
    }

    public function promosi()
    {
      return $this->belongsTo(Promosi::class, 'promosi_id', 'id');
    }
}