<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('role', ['kasir', 'koki', 'admin']);
        });

        Schema::create('meja', function (Blueprint $table) {
            $table->increments('id_meja');
            $table->string('nomor_meja', 20);
            $table->enum('status', ['kosong', 'terisi'])->default('kosong');
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id_menu');
            $table->string('kategori', 50);
            $table->string('nama_menu', 100);
            $table->decimal('harga', 10, 2);
            $table->text('deskripsi')->nullable();
            $table->integer('stok')->default(0);
        });

        Schema::create('pesanan', function (Blueprint $table) {
            $table->increments('id_pesanan');
            $table->string('kode_pesanan', 20);
            $table->unsignedInteger('id_meja');
            $table->string('no_meja', 20);
            $table->string('nama_pelanggan', 100);
            $table->string('no_hp_pelanggan', 20)->nullable();
            $table->unsignedInteger('id_user')->nullable();
            $table->decimal('total_bayar', 10, 2)->default(0);
            $table->enum('status_pesanan', ['diterima', 'dimasak', 'siap_disajikan', 'selesai'])->default('diterima');
            $table->enum('metode_pembayaran', ['tunai', 'qris']);
            $table->enum('status_pembayaran', ['pending', 'lunas'])->default('pending');
            $table->timestamp('tanggal_pesan')->useCurrent();

            $table->foreign('id_meja')->references('id_meja')->on('meja');
            $table->foreign('id_user')->references('id_user')->on('users');
        });

        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->increments('id_detail_pesanan');
            $table->unsignedInteger('id_pesanan');
            $table->string('no_meja', 20);
            $table->unsignedInteger('id_menu');
            $table->integer('jumlah');
            $table->decimal('subtotal', 10, 2);
            $table->text('catatan')->nullable();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
            $table->foreign('id_menu')->references('id_menu')->on('menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('meja');
        Schema::dropIfExists('users');
    }
};