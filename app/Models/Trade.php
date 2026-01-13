<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    // Izinkan semua kolom diisi
    protected $guarded = ['id'];

    // Beritahu Laravel bahwa kolom ini isinya Array/JSON, bukan text biasa
    protected $casts = [
        'entry_date' => 'datetime',
        'sop_data' => 'array',
    ];
}