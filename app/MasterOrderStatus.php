<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterOrderStatus extends Model
{
    protected $table = 'master_order_status';
    protected $fillable = ['name'];
    protected $hidden = ['created_at', 'updated_at'];
}
