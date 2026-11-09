<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PasangFotoMenu extends Command
{
    protected $signature = 'menu:pasang-foto';

    protected $description = 'Pasang foto ke menu berdasarkan nama file di public/images/menu (contoh: nasi-liwet-ayam.jpg)';

    public function handle(): int
    {
        $dir = public_path('images/menu');

        if (! is_dir($dir)) {
            $this->error("Folder belum ada: {$dir}");

            return self::FAILURE;
        }

        $berhasil = 0;

        foreach (Menu::orderBy('nama_menu')->get() as $menu) {
            $slug = Str::slug($menu->nama_menu);

            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                if (is_file($dir . DIRECTORY_SEPARATOR . "{$slug}.{$ext}")) {
                    $menu->update(['gambar' => "menu/{$slug}.{$ext}"]);
                    $this->info("OK  {$menu->nama_menu}  <-  {$slug}.{$ext}");
                    $berhasil++;
                    continue 2;
                }
            }

            $this->warn("--  {$menu->nama_menu}  (siapkan file: {$slug}.jpg)");
        }

        $this->line("Selesai: {$berhasil} menu sudah berfoto.");

        return self::SUCCESS;
    }
}
