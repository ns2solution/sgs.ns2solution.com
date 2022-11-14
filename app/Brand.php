<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
	use SoftDeletes;
	
    protected $table = 'brands';

    protected $fillable = [
    	'id_brand', 'principle_id', 'brand_logo',
    	'brand_name', 'code',
    	'created_by', 'updated_by',	'deleted_by'
    ];

    protected $primaryKey = 'id_brand';
}
