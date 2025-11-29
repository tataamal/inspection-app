<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryQualityManagement extends Model
{
    use HasFactory;
    protected $table = 'history_quality_management';
    protected $guarded = ['id'];

    protected $fillable = [
        'prueflos', 'plant', 'order_number', 
        'sales_order', 'sales_item', 'buyer_name', 'customer_po',
        'material_code', 'material_desc', 'batch', 'quantity', 'uom',
        'inspector_sap_id', 'inspector_nik', 'ud_code', 'ud_selected_set',
        'status', 'sap_message', 'full_lot_snapshot'
    ];

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
