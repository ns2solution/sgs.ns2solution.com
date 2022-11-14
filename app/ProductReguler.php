<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReguler extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'product_reguler';
    protected $fillable = ['reguler_name', 'created_by',	'updated_by',	'deleted_by'];
}
