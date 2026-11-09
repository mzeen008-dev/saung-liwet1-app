<?php

namespace Database\Seeders;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['nama' => 'Admin', 'username' => 'admin', 'password' => 'admin123', 'role' => 'admin']);
        User::create(['nama' => 'Kasir', 'username' => 'kasir', 'password' => 'kasir123', 'role' => 'kasir']);
        User::create(['nama' => 'Koki', 'username' => 'koki', 'password' => 'koki123', 'role' => 'koki']);

        foreach (range(1, 5) as $i) {
            Meja::create(['nomor_meja' => (string) $i]);
        }

        $menus = [
            ['Makanan', 'Nasi Liwet Ayam', 25000, 'Nasi liwet dengan ayam goreng', 50],
            ['Makanan', 'Nasi Liwet Ikan Asin', 22000, 'Nasi liwet dengan ikan asin', 50],
            ['Makanan', 'Tahu Tempe Goreng', 8000, 'Tahu dan tempe goreng', 50],
            ['Minuman', 'Es Teh Manis', 5000, 'Teh manis dingin', 100],
            ['Minuman', 'Es Jeruk', 7000, 'Jeruk peras dingin', 100],
        ];

        foreach ($menus as [$kategori, $nama, $harga, $deskripsi, $stok]) {
            Menu::create([
                'kategori' => $kategori,
                'nama_menu' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'stok' => $stok,
            ]);
        }
    }
}