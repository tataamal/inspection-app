<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorySubmitQm extends Model
{
    use HasFactory;
    
    protected $table = 'history_submit_qms';
    
    protected $fillable = [
        'username',
        'process_date',
        'aufnr',
        'maktx',
        'rueck',
        'rmzhl',
        'budat',
        'status',
        'message'
    ];

    protected $casts = [
        'process_date' => 'datetime',
        'budat' => 'date'
    ];
}
