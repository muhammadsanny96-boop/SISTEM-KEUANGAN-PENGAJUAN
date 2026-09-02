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
        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('harga_beli_satuan', 15, 2)->nullable()->after('total_biaya');
            $table->date('tanggal_realisasi')->nullable()->after('biaya_realisasi');
            $table->string('bukti_pembelian')->nullable()->after('foto_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['harga_beli_satuan', 'tanggal_realisasi', 'bukti_pembelian']);
        });
    }
};
