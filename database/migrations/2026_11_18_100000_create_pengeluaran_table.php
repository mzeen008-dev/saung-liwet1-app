<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengeluaran')) {
            Schema::create('pengeluaran', function (Blueprint $t) {
                $t->increments('id_pengeluaran');
                $t->date('tanggal')->index();
                $t->string('kategori', 30);
                $t->string('keterangan', 255);
                $t->decimal('jumlah', 10, 2);
                $t->unsignedInteger('id_user')->nullable();
                $t->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};