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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('nama_barang');
            $table->unsignedInteger('jumlah');
            $table->string('satuan');
            $table->enum('jenis_pengajuan', [
                'Barang Habis',
                'Barang Rusak',
                'Barang Perlu Diganti',
                'Barang Baru',
                'Barang Perlu Dibeli',
            ]);
            $table->enum('prioritas', ['Rendah', 'Sedang', 'Tinggi', 'Mendesak'])->default('Sedang');
            $table->text('alasan');
            $table->string('foto_barang')->nullable();
            $table->enum('status', ['Menunggu', 'Diproses', 'Disetujui', 'Ditolak', 'Selesai'])->default('Menunggu');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['division_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
