<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentType extends Model
{
    use SoftDeletes;

    protected $table = 'shipment_types';

    protected $fillable = [
        'cart_id', 'warehouse_id', 'courier_id',
        'courier_service', 'courier_desc', 'courier_ongkir',
        'weight', 'origin_id', 'destination_id', 'user_address_id', 'order_id'
    ];
}
