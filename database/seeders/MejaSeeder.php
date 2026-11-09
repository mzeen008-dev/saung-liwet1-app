<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meja;

class MejaSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Meja::create([
                'no' => $i,
                'no_meja' => 'Saung ' . sprintf('%02d', $i),
                'status' => 'kosong',
                'qr_code' => 'qr_saung_' . $i . '.png',
            ]);
        }
    }
}