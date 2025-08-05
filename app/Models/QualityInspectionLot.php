<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspectionLot extends Model
{
    use HasFactory;

    protected $table = 'quality_inspection_lots';

    protected $fillable = [
        'PRUEFLOS',
        'WERK',
        'ART',
        'HERKUNFT',
        'OBJNR',
        'ENSTEHDAT',
        'ENTSTEZEIT',
        'AUFNR',
        'DISPO',
        'ARBPL',
        'KTEXT',
        'ARBID',
        'KUNNR',
        'LIFNR',
        'HERSTELLER',
        'EMATNR',
        'MATNR',
        'CHARG',
        'LAGORTCHRG',
        'KDAUF',
        'KDPOS',
        'EBELN',
        'EBELP',
        'BLART',
        'MJAHR',
        'MBLNR',
        'ZEILE',
        'BUDAT',
        'BWART',
        'WERKVORG',
        'LAGORTVORG',
        'LS_KDPOS',
        'LS_VBELN',
        'LS_POSNR',
        'LS_ROUTE',
        'LS_KUNAG',
        'LS_VKORG',
        'LS_KDMAT',
        'SPRACHE',
        'KTEXTMAT',
        'LOSMENGE',
        'MENGENEINH',
        'LMENGE01',
        'LMENGE04',
        'LMENGE07',
        'LMENGEZUB',
        'STAT34',
        'STAT35',
        'KTEXTLOS',
        'INSP_DOC_NUMBER',
        'AUFPL',
        'STATS',
    ];

    protected $dates = [
        'ENSTEHDAT',
        'BUDAT',
    ];
}
