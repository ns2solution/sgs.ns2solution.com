<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
	use SoftDeletes;

    protected $table = 'user_profile';

    protected $fillable = [
        'user_id', 'phone', 'address', 'code',
        'place_id', 'subdistrict_id', 'postal_code', 'gender',
        'birth_date', 'photo', 'photo_ktp', 'warpay','point',
        'created_by', 'updated_by', 'deleted_by',
        'usr_addr_receiver_name', 'usr_addr_receiver_phone', 'usr_addr_primary', 'usr_addr_subdistrict_id',
        'usr_addr_address', 'usr_addr_address_label', 'usr_addr_postal_code',
    ];

    public function user()
	{
		return $this->hasOne('App\User', 'id', 'user_id');
    }
    
    public function usr_addr_subdistrict() {

        return $this->belongsTo(RajaongkirSubdistrict::class, 'usr_addr_subdistrict_id', 'subdistrict_id');
    
    }
}
