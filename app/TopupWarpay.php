<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopupWarpay extends Model
{
    protected $table = 'topup_warpay';

    protected $fillable = [
    	'status', 'user_id', 'total', 'order_payment_id', 'warpay_id'
    ];

    public function user()
	{
		return $this->hasOne('App\User', 'id', 'user_id');
    }
    
    public function warpay()
	{
		return $this->hasOne('App\Warpay', 'id', 'warpay_id');
	}
}   
