<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RajaongkirSubdistrict extends Model
{
    protected $table = 'rajaongkir_subdistrict';

    public $timestamps = false;

    protected $primaryKey = 'subdistrict_id';

    protected $fillable = [
        'subdistrict_id',  'province_id', 'city_id', 'subdistrict_name', ''
    ];

}
