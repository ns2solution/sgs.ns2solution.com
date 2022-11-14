<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierSetting extends Model
{
    use SoftDeletes;

    protected $table = 'courier_setting';

    protected $fillable = [
        'courier_id', 'warehouse_id', 'value'
    ];
}
