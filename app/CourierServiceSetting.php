<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierServiceSetting extends Model
{
    protected $table = 'courier_service_setting';

    protected $fillable = ['courier_service_id', 'warehouse_id', 'value'];
}
