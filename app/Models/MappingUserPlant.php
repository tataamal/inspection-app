<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingUserPlant extends Model
{
    // Tentukan nama tabel secara eksplisit
    protected $table = 'mapping_user_plant';

    // Izinkan semua kolom diisi secara massal
    protected $guarded = [];
}
