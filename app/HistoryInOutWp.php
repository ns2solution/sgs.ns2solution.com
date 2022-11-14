<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryInOutWp extends Model
{
    protected $table = 'history_in_out_wp';

    protected $fillable  = ['user_id', 'total', 'type', 'warpay_prev', 'by'];

}
