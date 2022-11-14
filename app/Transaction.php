<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
	use SoftDeletes;
	
    protected $table = 'transaction';

    protected $fillable = [
    	'id', 'trans_number', 'user_id',
    	'created_by', 'updated_by',	'deleted_by'
    ];

    // protected $primaryKey = 'id_brand';
}
