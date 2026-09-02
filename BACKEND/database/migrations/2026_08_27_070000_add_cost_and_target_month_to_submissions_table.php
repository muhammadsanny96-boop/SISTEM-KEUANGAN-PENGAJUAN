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
            $table->decimal('harga_satuan', 15, 2)->default(0)->after('satuan');
            $table->decimal('total_biaya', 15, 2)->default(0)->after('harga_satuan');
            $table->string('target_bulan', 7)->nullable()->after('total_biaya'); // Format: YYYY-MM (e.g. 2026-08)
            $table->decimal('biaya_realisasi', 15, 2)->nullable()->after('target_bulan');

            $table->index('target_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['target_bulan']);
            $table->dropColumn(['harga_satuan', 'total_biaya', 'target_bulan', 'biaya_realisasi']);
        });
    }
};
