<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TradeAnalyzer
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        // Masukkan API Key di file .env: GEMINI_API_KEY=your_key_here
        $this->apiKey = env('GEMINI_API_KEY');
        // Kita pakai model Gemini 1.5 Flash karena cepat & hemat
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    }

    /**
     * Fungsi utama untuk menganalisa chart
     * @param string $imageFullPath Path lengkap file di server
     * @return array JSON data hasil analisa
     */
    public function analyzeChart($imageFullPath)
    {
        // 1. Validasi File
        if (!file_exists($imageFullPath)) {
            throw new Exception("File gambar tidak ditemukan: " . $imageFullPath);
        }

        // 2. Encode Gambar ke Base64
        $imageData = base64_encode(file_get_contents($imageFullPath));
        $mimeType = mime_content_type($imageFullPath); // contoh: image/png

        // 3. Susun Prompt "SMC Trader"
        // Kita paksa output JSON Schema agar mudah disimpan di DB
        $promptText = "
            Act as a professional SMC (Smart Money Concept) & Price Action Trader.
            Analyze this chart image specifically for a trading setup.
            
            Please output ONLY valid JSON (no markdown formatting, no code blocks) with this exact structure:
            {
                'market_structure': 'Brief text (e.g., Bullish, Break of Structure Up)',
                'patterns_detected': ['List', 'of', 'patterns', 'e.g. FVG, Order Block, Engulfing'],
                'key_levels': 'Description of support/resistance/supply/demand',
                'candle_reaction': 'Description of the entry candle',
                'smc_context': 'Pro-Trend or Counter-Trend',
                'summary_comment': 'Short insight regarding the quality of this setup'
            }
        ";

        // 4. Kirim Request ke Google Gemini API
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $promptText],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2, // Rendah agar konsisten/tidak halusinasi
                    'response_mime_type' => 'application/json' // Fitur baru Gemini: Paksa JSON
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                throw new Exception('Gagal menghubungi AI Service.');
            }

            $responseData = $response->json();

            // 5. Ambil Text dari Respon Gemini
            // Struktur: candidates[0].content.parts[0].text
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            // 6. Parsing String JSON ke Array PHP
            $parsedData = json_decode($rawText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback jika AI menyertakan markdown ```json di awal
                $cleanText = str_replace(['```json', '```'], '', $rawText);
                $parsedData = json_decode($cleanText, true);
            }

            return $parsedData;

        } catch (Exception $e) {
            Log::error('Trade Analyzer Exception: ' . $e->getMessage());
            // Return array kosong atau default error agar aplikasi tidak crash
            return [
                'market_structure' => 'Error Analyzing',
                'patterns_detected' => [],
                'summary_comment' => 'AI Failed to process image: ' . $e->getMessage()
            ];
        }
    }
}