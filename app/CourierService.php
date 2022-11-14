<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierService extends Model
{
    protected $table = 'courier_service';

    protected $fillable = ['courier_id', 'service_code', 'service_name'];
}
