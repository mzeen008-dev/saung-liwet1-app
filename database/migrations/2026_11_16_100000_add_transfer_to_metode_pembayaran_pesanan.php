<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $col = DB::selectOne("SHOW COLUMNS FROM pesanan LIKE 'metode_pembayaran'");
        if ($col && stripos($col->Type, "'transfer'") === false) {
            $null = $col->Null === 'YES' ? 'NULL' : 'NOT NULL';
            $def  = $col->Default !== null ? "DEFAULT '" . addslashes($col->Default) . "'" : '';
            DB::statement("ALTER TABLE pesanan MODIFY metode_pembayaran ENUM('tunai','qris','transfer') $null $def");
        }
    }

    public function down(): void
    {
        DB::table('pesanan')->where('metode_pembayaran', 'transfer')->update(['metode_pembayaran' => 'tunai']);
        $col = DB::selectOne("SHOW COLUMNS FROM pesanan LIKE 'metode_pembayaran'");
        if ($col) {
            $null = $col->Null === 'YES' ? 'NULL' : 'NOT NULL';
            $def  = $col->Default !== null ? "DEFAULT '" . addslashes($col->Default) . "'" : '';
            DB::statement("ALTER TABLE pesanan MODIFY metode_pembayaran ENUM('tunai','qris') $null $def");
        }
    }
};