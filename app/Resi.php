<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resi extends Model
{
    use SoftDeletes;

    protected $table = 'resi';

    protected $fillable = [
    	'order_id', 'number_resi'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function order()
    {
      return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
