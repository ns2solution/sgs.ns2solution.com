<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'product_type';
    protected $fillable = ['product_type',	'created_by',   'updated_by', 'deleted_by'];
}