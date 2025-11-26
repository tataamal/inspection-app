<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryComponent extends Model
{
    protected $table = 'production_t_data4';

    protected $fillable = [
        'MANDT',
        'RSNUM',
        'RSPOS',
        'VORNR',
        'WERKS',
        'KDAUF',
        'KDPOS',
        'AUFNR',
        'PLNUM',
        'STATS',
        'DISPO',
        'MATNR',
        'MAKTX',
        'MEINS',
        'BAUGR',
        'WERKSX',
        'BDMNG',
        'KALAB',
        'VMENG',
        'SOBSL',
        'BESKZ',
        'LTEXT',
        'LGORT',
        'OUTSREQ',
    ];
}
