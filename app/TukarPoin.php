<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TukarPoin extends Model
{
    protected $table = 'product_poin';

    protected $fillable = [
        'title','product_image', 'type', 'product_name', 'warpay', 'min_poin', 'status', 'stok', 'created_by',
        'updated_by', 'deleted_by'
    ];
}
