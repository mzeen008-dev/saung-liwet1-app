<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['nama_menu' => 'Nasi Liwet Komplit', 'kategori' => 'makanan', 'harga' => 35000, 'stok' => 50, 'deskripsi' => 'Nasi liwet khas saung dengan ayam bakar, tahu, tempe, dan lalapan.'],
            ['nama_menu' => 'Ayam Goreng Serundeng', 'kategori' => 'makanan', 'harga' => 25000, 'stok' => 40, 'deskripsi' => 'Ayam goreng empuk bertabur serundeng gurih.'],
            ['nama_menu' => 'Ikan Gurame Bakar', 'kategori' => 'makanan', 'harga' => 65000, 'stok' => 20, 'deskripsi' => 'Ikan gurame segar dibakar dengan bumbu kecap spesial.'],
            ['nama_menu' => 'Es Jeruk Kelapa', 'kategori' => 'minuman', 'harga' => 12000, 'stok' => 100, 'deskripsi' => 'Segarnya perasan jeruk peras dipadu kelapa muda.'],
            ['nama_menu' => 'Teh Manis Hangat/Es', 'kategori' => 'minuman', 'harga' => 5000, 'stok' => 150, 'deskripsi' => 'Teh seduh alami manis dan menyegarkan.'],
        ];

        foreach ($menus as $index => $menu) {
            Menu::create([
                'no' => $index + 1,
                'nama_menu' => $menu['nama_menu'],
                'kategori' => $menu['kategori'],
                'harga' => $menu['harga'],
                'stok' => $menu['stok'],
                'deskripsi' => $menu['deskripsi'],
                'foto' => null,
            ]);
        }
    }
}