<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPointImage extends Model
{
    protected $table = 'product_point_image';
    protected $fillable = ['product_point_id', 'path','created_by',   'updated_by', 'deleted_by'];

}
