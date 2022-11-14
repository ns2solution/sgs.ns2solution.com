<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogPoint extends Model
{
    protected $table = 'log_point';

    protected $fillable = [
    	'user_id', 'point', 'total_before', 'total_after',
    	'type', 'message', 'by'
    ];
}
