<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alasan extends Model
{
	use SoftDeletes;
	
    protected $table = 'master_alasan';

    protected $fillable = [
        'alasan',
    	'created_by', 'updated_by',	'deleted_by'
    ];

    // protected $primaryKey = 'id_brand';
}
