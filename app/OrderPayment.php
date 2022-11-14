<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPayment extends Model
{
    use SoftDeletes;

    protected $table = 'order_payment';

    protected $fillable = [
    	'grand_total', 'payment_url',
        'payment_token','payment_status', 'payment_due'
    ];

    public const PAID   = 'paid';
	public const UNPAID = 'unpaid';

    public function isPaid()
	{
		return $this->payment_status == self::PAID;
	}
}
