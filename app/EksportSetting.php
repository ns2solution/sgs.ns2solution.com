<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EksportSetting extends Model
{
    //
    use SoftDeletes;
    protected $table = 'eksport_settings';

    protected $fillable = [
        'eksport_type_id', 'cetakan', 'tanggal_start',
        'tanggal_end'
    ];
}
