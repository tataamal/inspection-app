<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionHeader extends Model
{
    //protected $fillable = ['inspection_type_id', 'title'];

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function questions()
    {
        return $this->hasMany(InspectionQuestion::class, 'header_id');
    }

     protected $fillable = [
        'inspection_type_id',
        'title',
    ];
}
