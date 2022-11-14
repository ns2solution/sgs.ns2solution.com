<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use SoftDeletes;

    protected $table = 'user_address';

    protected $fillable = [
        'user_id', 'address_label', 'address',
        'subdistrict_id', 'receiver_name', 'postal_code',
        'receiver_phone', 'primary'
    ];

    public function subdistrict() {
        return $this->belongsTo(RajaongkirSubdistrict::class, 'subdistrict_id', 'subdistrict_id');
    }
}
