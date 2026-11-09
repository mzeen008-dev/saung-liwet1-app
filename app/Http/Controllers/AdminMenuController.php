<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminMenuController extends Controller
{
    // Stok di bawah atau sama dengan angka ini dianggap "menipis"
    private const BATAS_MENIPIS = 10;

    // Lebar maksimal foto yang disimpan (piksel)
    private const LEBAR_MAKS = 900;

    public function index()
    {
        $menus = Menu::orderBy('kategori')->orderBy('nama_menu')->get();
        $batas = self::BATAS_MENIPIS;

        $kategori = $menus->pluck('kategori')->unique()->values();
        $habis    = $menus->filter(fn ($m) => $m->stok <= 0)->count();
        $menipis  = $menus->filter(fn ($m) => $m->stok > 0 && $m->stok <= $batas)->values();
        $tersedia = $menus->count() - $habis;

        // "Hari ini" mengikuti tanggal MySQL supaya sama dengan zona waktu data pesanan
        $hariIni = Pesanan::where('status_pembayaran', 'lunas')->whereRaw('DATE(tanggal_pesan) = CURDATE()');
        $penjualan = (float) (clone $hariIni)->sum('total_bayar');
        $transaksi = (clone $hariIni)->count();

        $meja = Meja::selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'terisi'), 0) AS terisi")->first();

        return view('admin.menu.index', compact(
            'menus', 'batas', 'kategori', 'habis', 'menipis', 'tersedia', 'penjualan', 'transaksi', 'meja'
        ));
    }

    public function create()
    {
        return view('admin.menu.form', ['menu' => new Menu()]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $data['gambar'] = $this->simpanFoto($request->file('foto'));
        }

        Menu::create($data);

        return redirect()->route('admin.menu.index')->with('sukses', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menu.form', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $baru = $this->simpanFoto($request->file('foto'));
            $this->hapusFoto($menu->gambar);
            $data['gambar'] = $baru;
        } elseif ($request->boolean('hapus_foto')) {
            $this->hapusFoto($menu->gambar);
            $data['gambar'] = null;
        }

        $menu->update($data);

        return redirect()->route('admin.menu.index')->with('sukses', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        // Menu yang sudah pernah dipesan tidak boleh dihapus (menjaga riwayat pesanan)
        if (DetailPesanan::where('id_menu', $menu->id_menu)->exists()) {
            return back()->with('info', "Menu {$menu->nama_menu} sudah pernah dipesan, jadi tidak bisa dihapus. Set stok ke 0 kalau ingin menyembunyikannya dari pemesanan.");
        }

        $this->hapusFoto($menu->gambar);
        $menu->delete();

        return redirect()->route('admin.menu.index')->with('sukses', 'Menu berhasil dihapus.');
    }

    // Ubah stok cepat langsung dari tabel
    public function stok(Request $request, Menu $menu)
    {
        $stok = filter_var($request->input('stok'), FILTER_VALIDATE_INT);

        if ($stok === false || $stok < 0 || $stok > 100000) {
            return back()->with('info', 'Stok harus berupa angka bulat antara 0 dan 100000.');
        }

        $menu->update(['stok' => $stok]);

        return back()->with('sukses', "Stok {$menu->nama_menu} diperbarui menjadi {$stok}.");
    }

    public function export()
    {
        $namaFile = 'daftar-menu-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // supaya Excel membaca UTF-8 dengan benar

            fputcsv($out, ['Kategori', 'Nama Menu', 'Harga', 'Stok', 'Deskripsi'], ',', '"', '\\');

            foreach (Menu::orderBy('kategori')->orderBy('nama_menu')->get() as $m) {
                fputcsv($out, [
                    $this->aman($m->kategori),
                    $this->aman($m->nama_menu),
                    $m->harga,
                    $m->stok,
                    $this->aman($m->deskripsi),
                ], ',', '"', '\\');
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validasi(Request $request): array
    {
        $valid = $request->validate([
            'kategori'   => ['required', 'string', 'max:50'],
            'nama_menu'  => ['required', 'string', 'max:100'],
            'harga'      => ['required', 'numeric', 'min:0'],
            'deskripsi'  => ['nullable', 'string'],
            'stok'       => ['required', 'integer', 'min:0'],
            'foto'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hapus_foto' => ['nullable', 'boolean'],
        ], [
            'foto.mimes'    => 'Foto harus berupa gambar JPG, PNG, atau WEBP.',
            'foto.max'      => 'Ukuran foto maksimal 4 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak melebihi batas server (cek upload_max_filesize di php.ini).',
        ]);

        // Kolom foto dan hapus_foto bukan kolom tabel
        return Arr::except($valid, ['foto', 'hapus_foto']);
    }

    // Simpan foto di public/images/menu: dikecilkan, diputar sesuai orientasi kamera, dan disimpan ulang
    // sebagai JPG dengan nama acak (membuang metadata dan isi tersembunyi dari file asli).
    private function simpanFoto(UploadedFile $file): string
    {
        $dir = public_path('images/menu');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nama = Str::lower(Str::random(24));
        $sumber = $file->getRealPath();
        $info = @getimagesize($sumber);

        if (extension_loaded('gd') && $info) {
            $gambar = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($sumber),
                IMAGETYPE_PNG  => @imagecreatefrompng($sumber),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sumber) : false,
                default        => false,
            };

            if ($gambar) {
                // Foto dari HP sering menyimpan orientasi di metadata: putar supaya tidak miring
                if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($sumber);
                    $sudut = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
                    if ($sudut) {
                        $putar = imagerotate($gambar, $sudut, 0);
                        if ($putar) {
                            $gambar = $putar;
                        }
                    }
                }

                if (imagesx($gambar) > self::LEBAR_MAKS) {
                    $kecil = imagescale($gambar, self::LEBAR_MAKS);
                    if ($kecil) {
                        $gambar = $kecil;
                    }
                }

                // Latar putih supaya PNG/WEBP transparan tidak jadi hitam saat disimpan sebagai JPG
                $w = imagesx($gambar);
                $h = imagesy($gambar);
                $kanvas = imagecreatetruecolor($w, $h);
                imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
                imagecopy($kanvas, $gambar, 0, 0, 0, 0, $w, $h);
                imagejpeg($kanvas, $dir . DIRECTORY_SEPARATOR . $nama . '.jpg', 82);

                return 'menu/' . $nama . '.jpg';
            }
        }

        // Cadangan kalau ekstensi GD tidak aktif: simpan apa adanya dengan ekstensi yang aman
        $ext = in_array($file->guessExtension(), ['jpg', 'jpeg', 'png', 'webp'], true) ? $file->guessExtension() : 'jpg';
        $file->move($dir, $nama . '.' . $ext);

        return 'menu/' . $nama . '.' . $ext;
    }

    private function hapusFoto(?string $gambar): void
    {
        if ($gambar && str_starts_with($gambar, 'menu/') && ! str_contains($gambar, '..')) {
            @unlink(public_path('images/' . $gambar));
        }
    }

    // Cegah "CSV injection": sel yang diawali = + - @ akan dibaca Excel sebagai rumus
    private function aman($nilai): string
    {
        $nilai = (string) $nilai;

        return preg_match('/^[=+\-@\t\r]/', $nilai) ? "'" . $nilai : $nilai;
    }
}
