<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPoint extends Model
{
    use SoftDeletes;

    protected $table = 'product_point';

    protected $fillable = [
        'prod_number', 'prod_barcode_number', 'prod_universal_number', 'prod_name', 'prod_modal_price', 'prod_base_price', 'prod_gram', 'prod_satuan', 'prod_reguler_id', 'principle_id', 'category_id', 'sub_category_id', 'prod_description', 'prod_type_id', 'prod_status_id', 'brand_id', 'prod_base_point', 'diskon', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by',
    ];

    public function stock()
    {
      return $this->hasMany(StockProductPoint::class, 'product_point_id', 'id');
    }

    public function image()
    {
      return $this->hasMany(ProductPointImage::class, 'product_point_id', 'id');
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
