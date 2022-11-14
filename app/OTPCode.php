<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OTPCode extends Model
{
	use SoftDeletes;
	
    protected $table = 'otp_code';

    protected $fillable = [
    	'email', 'otp', 'type', 'expired_at'
    ];
}
