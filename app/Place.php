<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $table = 'rajaongkir_city';

    protected $primaryKey = 'city_id';

    public $timestamps = false;

    protected $fillable = [
        'city_id', 'province_id', 'province',
        'type', 'city_name', 'postal_code', 'warehouse_id'
    ];

    public function provinsi()
    {
    	return $this->hasOne('App\MasterProvinsi', 'province_id', 'province_id');
    }
}
