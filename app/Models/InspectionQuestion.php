<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionQuestion extends Model
{
    protected $fillable = ['header_id', 'question_text'];

    public function header()
    {
        return $this->belongsTo(InspectionHeader::class, 'header_id');
    }
}
