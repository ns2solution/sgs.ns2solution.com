<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warpay extends Model
{
    protected $table = 'warpay';

    protected $fillable = [
        'total'
    ];
}
