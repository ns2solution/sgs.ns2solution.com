<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AkumulasiPointPerMonth extends Model
{
    use SoftDeletes;

    protected $table = 'akumulasi_point_per_month';

    protected $fillable = [
        'amount', 'user_id', 'is_checked_with_cron', 'type_transaction', 'order_id'
    ];
}
