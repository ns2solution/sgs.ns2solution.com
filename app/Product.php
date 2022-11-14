<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use SoftDeletes;

    protected $table = 'product';

    protected $fillable = [
        'prod_number',	'prod_barcode_number',	'prod_universal_number',	
        'prod_name', 'prod_modal_price',	'prod_base_price',	
        'prod_gram',	'principle_id',	'category_id',	'prod_satuan',
        'sub_category_id', 'prod_description',	'prod_type_id',	
        'prod_status_id',	'brand_id',	'min_poin', 'diskon', 
        'created_by',	'updated_by',	'deleted_by', 'deleted_at'
    ];

    public function stock()
    {
      return $this->hasMany(StockProduct::class, 'product_id', 'id');
    }

    public function image()
    {
      return $this->hasMany(ProductImage::class, 'id_product', 'id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'prod_type_id', 'id');
    }

    public function principle()
    {
        return $this->belongsTo(Principle::class, 'principle_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id_brand');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function sub_category()
    {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'prod_status_id', 'id');
    }


}

