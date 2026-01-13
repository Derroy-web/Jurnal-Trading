<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    // Field yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'pair',             // XAUUSD, BTCUSD
        'timeframe',        // M15, H1
        'direction',        // LONG, SHORT
        'screenshot_path',  // Path file gambar di storage
        'ai_analysis_data', // JSON hasil analisa AI
        'result',           // OPEN, WIN, LOSS, BE
        'pnl',              // Profit/Loss angka
        'rr_obtained',      // Risk Reward real
        'system_grade',     // Grade otomatis (A, B, C)
        'entry_date',       
    ];

    // Casting otomatis agar kolom JSON di database langsung jadi Array di PHP
    protected $casts = [
        'ai_analysis_data' => 'array', 
        'entry_date' => 'datetime',
        'pnl' => 'decimal:2',
    ];

    /**
     * Helper untuk mendapatkan warna badge berdasarkan Grade
     * Bisa dipanggil di Blade: $trade->grade_color
     */
    public function getGradeColorAttribute()
    {
        return match($this->system_grade) {
            'A' => 'success', // Hijau (Bootstrap/Tailwind)
            'B' => 'warning', // Kuning
            'C' => 'danger',  // Merah
            default => 'secondary',
        };
    }
}