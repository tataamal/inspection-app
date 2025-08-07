<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionType extends Model
{
    protected $fillable = ['name'];

    public function headers()
    {
        return $this->hasMany(InspectionHeader::class);
    }
}
