<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;


class Promosi extends Model
{
    use Authorizable, SoftDeletes;

    protected $table = 'promosi';

    protected $fillable = [
        'promosi_name', 'promosi_image', 'promosi_type',
        'start_date','end_date', 'created_by',
        'updated_by', 'deleted_by','warehouse_id','total_value',
        'total_bundle','info_bundle_id'
    ];

    public function warehouse()
    {
      return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function promosi_item()
    {
      return $this->hasMany(PromosiItem::class, 'promosi_id', 'id');
    }

    public function promosi_type_detail()
    {
      return $this->belongsTo(PromosiType::class, 'promosi_type', 'id');
    }

    public function bundle()
    {
      return $this->belongsTo(PromosiInfoBundle::class, 'info_bundle_id', 'id');
    }
}
