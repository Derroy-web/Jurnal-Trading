<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Services\TradeAnalyzer;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    // Halaman Dashboard: Menampilkan semua trade & gradenya
    public function index()
    {
        // Urutkan dari trade terbaru
        $trades = Trade::latest()->get();
        return view('trades.index', compact('trades'));
    }

    // Halaman Form Upload
    public function create()
    {
        return view('trades.create');
    }

    // Proses Simpan & Analisa AI
    public function store(Request $request, TradeAnalyzer $analyzer)
    {
        // 1. Validasi Input
        $request->validate([
            'pair' => 'required|string',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:4096', // Max 4MB
            'direction' => 'required|in:LONG,SHORT',
        ]);

        try {
            // 2. Simpan Gambar ke Storage
            // File akan masuk ke storage/app/public/screenshots
            $path = $request->file('screenshot')->store('screenshots', 'public');
            $fullPath = storage_path('app/public/' . $path);

            // 3. Panggil AI Service untuk Analisa
            $aiResult = $analyzer->analyzeChart($fullPath);

            // 4. Hitung Grade Sederhana (Versi Awal)
            // Nanti bisa dipercanggih dengan cek history database
            $grade = 'C'; // Default
            
            // Logika Grading Sementara:
            // Kalau AI mendeteksi 'FVG' atau 'Liquidity' dan trend searah (Pro-Trend) -> Grade A
            $patterns = $aiResult['patterns_detected'] ?? [];
            $context = $aiResult['smc_context'] ?? '';
            
            // Contoh Logic grading sederhana
            $isHighProb = false;
            foreach($patterns as $pat) {
                if (stripos($pat, 'FVG') !== false || stripos($pat, 'Order Block') !== false) {
                    $isHighProb = true;
                }
            }

            if ($isHighProb && stripos($context, 'Pro-Trend') !== false) {
                $grade = 'A';
            } elseif ($isHighProb) {
                $grade = 'B';
            }

            // 5. Simpan ke Database
            Trade::create([
                'pair' => strtoupper($request->pair),
                'direction' => $request->direction,
                'screenshot_path' => $path,
                'ai_analysis_data' => $aiResult, // JSON otomatis masuk
                'system_grade' => $grade,
                'entry_date' => now(),
                'result' => 'OPEN'
            ]);

            return redirect()->route('trades.index')->with('success', 'Trade berhasil dianalisa AI!');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $trade = Trade::findOrFail($id);
        return view('trades.edit', compact('trade'));
    }

    public function update(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);

        // Validasi data input
        $request->validate([
            'result' => 'required|in:WIN,LOSS,BE',
            'pnl' => 'numeric|nullable',
            'rr_obtained' => 'numeric|nullable',
            'session' => 'string|nullable',
            'note' => 'string|nullable',
        ]);

        // Update data trade
        $trade->update([
            'result' => $request->result,
            'pnl' => $request->pnl,
            'rr_obtained' => $request->rr_obtained,
            'session' => $request->session,
            'note' => $request->note,
        ]);

        // Opsional: Logic Auto-Grade Lanjutan
        // Jika user menyimpan hasil 'WIN' dan setupnya sesuai AI, grade bisa dinaikkan
        
        return redirect()->route('trades.index')->with('success', 'Trade updated successfully!');
    }
}