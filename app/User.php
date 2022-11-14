<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;

    protected $fillable = [
        'fullname', 'email', 'password',
        'pin', 'role', 'active', 'token', 'device_token',
        'wh_id', 'created_by', 'updated_by', 'email_confirmed'
    ];

    protected $hidden = [
        'password', 'pin', 'token',
        'profile'
    ];

    public function profile()
	{
		return $this->hasOne('App\UserProfile', 'user_id');
	}

    public function user_role()
    {
        return $this->hasOne('App\UserRole', 'id', 'role');
    }
}