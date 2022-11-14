<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryInOutPoint extends Model
{
    protected $table = 'history_in_out_point';

    protected $fillable  = ['user_id', 'total', 'type', 'message'];
}
