<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityInspectionLot extends Model
{
    protected $table = 'quality_inspection_lots';
    protected $primaryKey = 'PRUEFLOS';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
