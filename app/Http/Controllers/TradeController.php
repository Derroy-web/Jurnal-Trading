<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Storage;

class TradeController extends Controller
{
    // =================================================================
    // 1. HALAMAN UTAMA (FORM + DASHBOARD + LOG)
    // =================================================================
    public function create()
    {
        // --- A. DATA DASHBOARD ---
        $totalTrades = Trade::count();
        $wins = Trade::where('result', 'WIN')->count();
        $losses = Trade::where('result', 'LOSS')->count();
        
        // Win Rate
        $winRate = $totalTrades > 0 ? round(($wins / $totalTrades) * 100, 1) : 0;
        
        // Total Profit ($)
        $totalProfit = Trade::sum('profit_loss');
        
        // Growth (%) - Asumsi trade pertama adalah modal awal
        $firstTrade = Trade::orderBy('id', 'asc')->first();
        $initialBalance = $firstTrade ? $firstTrade->account_balance : 0; 
        $totalGainPercent = ($initialBalance > 0) ? round(($totalProfit / $initialBalance) * 100, 2) : 0;

        // Setup Terbaik (AI)
        $bestSetup = Trade::where('result', 'WIN')
                    ->select('setup_type', Trade::raw('count(*) as total'))
                    ->groupBy('setup_type')
                    ->orderByDesc('total')
                    ->first();

        // --- B. DATA FORM SOP ---
        $sopList = [
            'market_trending' => 'Apakah market sedang Trending/Jelas?',
            'setup_valid' => 'Apakah ada Setup (SMC/Price Action) Valid?',
            'risk_management' => 'Apakah Risk per trade max 1%?',
            'no_news' => 'Apakah tidak ada News High Impact 15 menit lagi?',
            'mental_state' => 'Kondisi Mental (Tenang & Fokus)?',
        ];

        // --- C. DATA TABEL LOG ---
        $trades = Trade::latest()->paginate(5);

        return view('trades.create', compact(
            'sopList', 'trades', 
            'totalTrades', 'wins', 'losses', 'winRate', 
            'bestSetup', 'totalProfit', 'totalGainPercent'
        ));
    }

    // =================================================================
    // 2. SIMPAN TRADE BARU (REQ KE AI PERTAMA KALI)
    // =================================================================
    public function store(Request $request)
    {
        $request->validate([
            'pair' => 'required',
            'chart_image' => 'required|image|max:4096', // Max 4MB
        ]);

        // Upload Gambar
        $imagePath = $request->file('chart_image')->store('charts', 'public');
        
        // Siapkan Gambar untuk AI
        $imageContent = file_get_contents(storage_path('app/public/' . $imagePath));
        $base64Image = base64_encode($imageContent);

        // Cek SOP
        $sopListLabels = [
            'market_trending' => 'Market Trending',
            'setup_valid' => 'Setup Valid',
            'risk_management' => 'Risk Management Aman',
            'no_news' => 'No News High Impact',
            'mental_state' => 'Mental Stabil',
        ];
        
        $userSop = $request->input('sop', []); 
        $violations = [];

        foreach ($sopListLabels as $key => $label) {
            if (!isset($userSop[$key])) {
                $violations[] = $label;
            }
        }

        $violationText = empty($violations) 
            ? "Saya DISIPLIN. Tidak ada pelanggaran SOP." 
            : "SAYA MELANGGAR SOP: " . implode(', ', $violations);

        // Prompt AI 1 (Identifikasi Setup)
        $prompt = "
        Role: Mentor Trading (SMC/Price Action).
        Konteks: Saya entry {$request->position} di {$request->pair}.
        SOP Status: {$violationText}
        
        Tugas:
        1. Identifikasi Setup Type (Contoh: Breakout, CHOCH, Liquidity Sweep, Inducement).
        2. Kritik chart ini.
        3. Prediksi Win Rate.
        
        FORMAT JSON WAJIB:
        {
            \"setup_type\": \"Nama Setup\",
            \"analysis\": \"Penjelasan singkat...\",
            \"win_rate\": \"High/Medium/Low\"
        }
        ";

        // Call Gemini
        $apiKey = env('GEMINI_API_KEY');
        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [['parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $base64Image]]
                ]]]
            ]);

            $aiData = $response->json();
            $rawText = $aiData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $rawText = str_replace(['```json', '```'], '', $rawText);
            $analysisResult = json_decode($rawText, true);
        } catch (\Exception $e) {
            $analysisResult = ['setup_type' => 'Manual', 'analysis' => 'AI Error: ' . $e->getMessage()];
        }

        // Simpan Data
        $finalNote = "⚠️ SOP Check: " . $violationText . "\n\n";
        $finalNote .= "🤖 Pre-Trade Analysis:\n" . ($analysisResult['analysis'] ?? '-');

        Trade::create([
            'entry_date' => now(),
            'pair' => $request->pair,
            'session' => $request->session,
            'position' => $request->position,
            'account_balance' => $request->account_balance ?? 0,
            'setup_type' => $analysisResult['setup_type'] ?? 'Unknown',
            'trade_note' => $finalNote,
            'chart_image' => $imagePath,
            'sop_data' => $userSop,
        ]);

        return redirect()->route('trades.create')->with('success', 'Trade dicatat!');
    }

    // =================================================================
    // 3. UPDATE STATUS (REQ KE AI KEDUA KALI)
    // =================================================================
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'pnl' => 'required|numeric',
            'result' => 'required|in:WIN,LOSS'
        ]);

        $trade = Trade::findOrFail($id);
        
        // A. Update Keuangan
        $pnl = abs($request->pnl); 
        if ($request->result == 'LOSS') $pnl = -$pnl;

        $trade->result = $request->result;
        $trade->profit_loss = $pnl;
        $trade->equity = $trade->account_balance + $pnl;
        $trade->percentage_change = ($trade->account_balance > 0) ? ($pnl / $trade->account_balance) * 100 : 0;

        // B. Update Analisa AI (Evaluasi Akhir)
        if ($trade->chart_image && Storage::disk('public')->exists($trade->chart_image)) {
            try {
                $imageContent = file_get_contents(storage_path('app/public/' . $trade->chart_image));
                $base64Image = base64_encode($imageContent);
                $apiKey = env('GEMINI_API_KEY');

                // Konteks Prompt Berbeda untuk Win/Loss
                $evalContext = ($request->result == 'WIN') 
                    ? "Trade ini WIN. Jelaskan kenapa setup ini berhasil? Apa kuncinya?" 
                    : "Trade ini LOSS. Cek chart lagi. Apakah ini Bad Analysis (kesalahan teknis) atau cuma Bad Luck (setup valid kena news)?";

                $prompt = "
                Role: Mentor Trading.
                Status Akhir: {$request->result} (PnL: \${$pnl}).
                Tugas: {$evalContext}
                Jawab singkat maksimal 2 kalimat.
                ";

                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $base64Image]]
                    ]]]
                ]);

                $aiFeedback = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Append Note
                $trade->trade_note .= "\n\n➖➖➖➖➖➖➖➖\n";
                $trade->trade_note .= "📢 RESULT ({$request->result}): " . trim($aiFeedback);

            } catch (\Exception $e) {
                // Silent fail agar user tetap bisa simpan PnL meski AI error
            }
        }

        $trade->save();
        return redirect()->back()->with('success', 'Status & Evaluasi AI berhasil disimpan!');
    }

    // =================================================================
    // 4. HAPUS DATA
    // =================================================================
    public function destroy($id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->chart_image) Storage::disk('public')->delete($trade->chart_image);
        $trade->delete();
        return redirect()->back()->with('success', 'Data dihapus.');
    }
}