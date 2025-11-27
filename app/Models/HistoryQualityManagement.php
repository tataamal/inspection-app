<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryQualityManagement extends Model
{
    use HasFactory;
    protected $table = 'history_quality_management';
    protected $guarded = ['id'];

    protected $casts = [
        'full_lot_snapshot' => 'array', // Auto convert JSON
        'quantity' => 'decimal:3',
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
