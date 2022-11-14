<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POPrinciple extends Model
{
    use SoftDeletes;

    protected $table = 'po_principle';

    protected $fillable = [
    	'no_po', 'order_id', 'principle_id'
    ];
}
