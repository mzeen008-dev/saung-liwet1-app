<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Uang tunai yang diterima kasir. Kembalian dihitung: uang_diterima - total_bayar.
            $table->decimal('uang_diterima', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('uang_diterima');
        });
    }
};
