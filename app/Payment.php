<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $table = 'payment';

    protected $fillable = [
		'order_id',
		'transaction_id',
		'amount',
		'method',
		'status',
		'token',
		'payloads',
		'payment_type',
		'va_number',
		'vendor_name',
		'biller_code',
		'bill_key',
    ];

    public const PAYMENT_CHANNELS = ['credit_card', 'mandiri_clickpay', 'cimb_clicks',
	'bca_klikbca', 'bca_klikpay', 'bri_epay', 'echannel', 'permata_va',
	'bca_va', 'bni_va', 'other_va', 'gopay', 'indomaret',
    'danamon_online', 'akulaku'];

    public const EXPIRY_DURATION = 24; // 24
    public const EXPIRY_UNIT = ['day' => 'day', 'minute' => 'minute', 'hour' => 'hour'];

    public const CHALLENGE = 'challenge';
	public const SUCCESS = 'success';
	public const SETTLEMENT = 'settlement';
	public const PENDING = 'pending';
	public const DENY = 'deny';
	public const EXPIRE = 'expire';
	public const CANCEL = 'cancel';


    public const PAYMENTCODE = 'PAY';

}
