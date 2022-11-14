<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterProvinsi extends Model
{
    protected $table = 'rajaongkir_province';

    protected $primaryKey = 'province_id';

	public $timestamps = false;
}
