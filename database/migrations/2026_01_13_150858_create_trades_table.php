<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->string('pair'); // XAUUSD, GBPUSD
            $table->string('timeframe'); // M15, H1, H4
            $table->string('direction'); // LONG / SHORT
            $table->string('screenshot_path'); // Path gambar chart
            
            // JANTUNGNYA SISTEM INI:
            // Menyimpan hasil analisa AI (Trend, Pattern, FVG, dll) dalam format JSON
            $table->json('ai_analysis_data')->nullable(); 
            
            // Hasil Trade (diisi setelah trade selesai)
            $table->enum('result', ['WIN', 'LOSS', 'BE', 'OPEN'])->default('OPEN');
            $table->decimal('pnl', 10, 2)->nullable(); // Profit/Loss dalam $
            $table->decimal('rr_obtained', 5, 2)->nullable(); // Risk Reward yang didapat
            
            // Auto-Calculated Grade
            $table->string('system_grade')->nullable(); // A, B, C (dari logika coding nanti)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
