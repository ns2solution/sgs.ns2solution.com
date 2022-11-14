<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_image';
    protected $fillable = ['id_product', 'path','created_by',   'updated_by', 'deleted_by'];

}
