<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    protected $table = 'product_status';
    protected $fillable = ['status_name', 'created_by',	'updated_by',	'deleted_by'];
}
