<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'order';

    protected $fillable = [
    	'no_po', 'status', 'warehouse_id',
        'user_id', 'total_price', 'total_ongkir',
        'payment_type', 'final_total', 'cancel_msg', 'order_payment_id',
        'is_pick', 'shipped_start_date', 'shipped_end_date',
        'is_dropshipper', 'dropshipper_name', 'dropshipper_number', 'is_accept_refund'
    ];
}
