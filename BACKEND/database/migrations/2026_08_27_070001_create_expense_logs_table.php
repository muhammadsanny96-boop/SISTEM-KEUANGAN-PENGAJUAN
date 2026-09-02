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
        Schema::create('expense_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('tipe', 50); // e.g. Pengajuan Baru, Persetujuan Anggaran, Penyesuaian Biaya, Realisasi Selesai, Pembatalan
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('bulan_periode', 7); // Format: YYYY-MM
            $table->text('keterangan');
            $table->timestamps();

            $table->index(['bulan_periode', 'division_id']);
            $table->index('submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_logs');
    }
};
