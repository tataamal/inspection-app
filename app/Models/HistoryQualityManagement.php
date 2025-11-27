<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryQualityManagement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'full_lot_snapshot' => 'array', 
        'quantity' => 'decimal:3',
        'ud_code' => 'string',
    ];
    public function getOriginalArtAttribute()
    {
        return $this->full_lot_snapshot['ART'] ?? null;
    }
    public function scopeSuccess($query)
    {
        return $query->where('status', 'SUCCESS');
    }
}
