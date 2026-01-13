<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            
            // 1. Info Dasar (Sesuai Kolom CSV)
            $table->dateTime('entry_date'); // Date and Time
            $table->string('pair');         // XAU/USD, dll
            $table->string('session');      // New York, London
            $table->enum('position', ['LONG', 'SHORT']); // LONG/SHORT
            
            // 2. Analisa (Diisi AI atau Manual)
            $table->string('setup_type')->nullable(); // Setup Type (SMC, Breakout, dll)
            
            // 3. Keuangan (Sesuai CSV)
            $table->decimal('account_balance', 15, 2); // Modal saat entry
            $table->decimal('risk_reward', 5, 2)->nullable(); // Risk to Reward (misal 3.5)
            $table->enum('result', ['WIN', 'LOSS', 'BE', 'OPEN'])->default('OPEN'); // Status
            $table->decimal('profit_loss', 15, 2)->nullable(); // Nominal Profit/Loss
            $table->decimal('equity', 15, 2)->nullable(); // Saldo akhir
            $table->decimal('percentage_change', 8, 2)->nullable(); // % Change

            // 4. Data Tambahan untuk Fitur Web
            $table->string('chart_image'); // Path file gambar yang diupload
            $table->text('trade_note')->nullable(); // Catatan gabungan (AI + User)
            
            // 5. SOP (Simpan sebagai JSON agar fleksibel checklistnya)
            // Ini akan menyimpan data seperti: {"trend_jelas": true, "mental_aman": false}
            $table->json('sop_data')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};