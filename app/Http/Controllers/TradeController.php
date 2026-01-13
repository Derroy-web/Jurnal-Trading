<?php

namespace App\Http\Controllers;

use App\Services\TradeAnalyzer;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function store(Request $request, TradeAnalyzer $analyzer)
    {
        // 1. Upload Gambar
        $path = $request->file('screenshot')->store('screenshots', 'public');

        // 2. Minta AI Menganalisa
        $analysisResult = $analyzer->analyzeChart(storage_path('app/public/'.$path));
        // Contoh output AI: ['patterns_detected' => ['FVG', 'Liquidity Sweep'], ...]

        // 3. HITUNG PROBABILITAS (Auto-Grade Logic)
        // Cari trade masa lalu yang punya pattern SAMA persis
        $patterns = $analysisResult['patterns_detected'];
        
        // Query ajaib Laravel JSON:
        $similarTrades = Trade::whereJsonContains('ai_analysis_data->patterns_detected', $patterns)
                            ->where('result', '!=', 'OPEN') // Hanya yang sudah selesai
                            ->get();

        $grade = 'N/A'; // Default kalau data belum cukup
        
        if ($similarTrades->count() > 5) { // Minimal 5 data biar valid
            $wins = $similarTrades->where('result', 'WIN')->count();
            $winRate = $wins / $similarTrades->count();

            if ($winRate >= 0.7) $grade = 'A+ (High Prob)';
            elseif ($winRate >= 0.5) $grade = 'B (Standard)';
            else $grade = 'C (Risky)';
        }

        // 4. Simpan ke Database
        Trade::create([
            'pair' => $request->pair,
            'screenshot_path' => $path,
            'ai_analysis_data' => $analysisResult, // Simpan JSON mentah dari AI
            'system_grade' => $grade, // Grade otomatis dari data masa lalumu
            // ... field lain
        ]);

        return redirect()->back()->with('success', 'Trade logged! AI Grade: ' . $grade);
    }
}
