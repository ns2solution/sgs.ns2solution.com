<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromosiType extends Model
{
    //
    use Authorizable, SoftDeletes;

    protected $table = 'promosi_type';

    protected $fillable = [
        'promosi_type', 'created_by',
        'updated_by', 'deleted_by'
    ];
}
