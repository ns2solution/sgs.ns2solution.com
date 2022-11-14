<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
    	'maintenance_apps', 'maintenance_mobile', 'point_birthday',
    	'convertion_warpay'
    ];
}