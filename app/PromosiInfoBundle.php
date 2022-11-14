<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromosiInfoBundle extends Model
{
    //
    use Authorizable, SoftDeletes;

    protected $table = 'promosi_info_bundle';

    protected $fillable = [
        'info_bundle', 'created_by',
        'updated_by', 'deleted_by'
    ];
}
