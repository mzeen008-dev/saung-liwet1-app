<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Kode pesanan harus unik, dan dipakai untuk mencari pesanan di halaman status
            $table->unique('kode_pesanan', 'pesanan_kode_unique');
            // Dipakai KDS, Kasir POS, dan laporan (filter status dan tanggal)
            $table->index(['status_pembayaran', 'status_pesanan'], 'pesanan_status_index');
            $table->index('tanggal_pesan', 'pesanan_tanggal_index');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropUnique('pesanan_kode_unique');
            $table->dropIndex('pesanan_status_index');
            $table->dropIndex('pesanan_tanggal_index');
        });
    }
};
