<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu', 'aktif')) {
            Schema::table('menu', function (Blueprint $t) {
                $t->boolean('aktif')->default(true)->after('gambar');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu', 'aktif')) {
            Schema::table('menu', function (Blueprint $t) {
                $t->dropColumn('aktif');
            });
        }
    }
};