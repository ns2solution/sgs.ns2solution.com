<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SelfPickedUp extends Model
{
    protected $table = 'self_picked_up_order';
    protected $fillable = ['cart_id', 'warehouse_id', 'status_pick'];

}
