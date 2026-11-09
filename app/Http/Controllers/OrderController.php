<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\DetailPesanan;

class OrderController extends Controller
{
    public function index($id_meja)
    {
        $meja = Meja::findOrFail($id_meja);

        // "Spesial hari ini": menu berfoto yang masih tersedia, bergantian tiap hari
        $kandidat = Menu::whereNotNull('gambar')->where('stok', '>', 0)->orderBy('id_menu')->get();
        $spesial = $kandidat->isEmpty() ? null : $kandidat[now()->dayOfYear % $kandidat->count()];

        return view('order.beranda', compact('meja', 'spesial'));
    }

    public function menu($id_meja)
    {
        $meja = Meja::findOrFail($id_meja);
        $menus = Menu::orderBy('kategori')->orderBy('nama_menu')->get();

        return view('order.menu', compact('meja', 'menus'));
    }

    public function checkoutPage($id_meja)
    {
        $meja = Meja::findOrFail($id_meja);
        $menus = Menu::orderBy('kategori')->orderBy('nama_menu')->get();

        return view('order.checkout', compact('meja', 'menus'));
    }

    public function status($kode)
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();
        $meja = Meja::findOrFail($pesanan->id_meja);

        return view('order.status', compact('kode', 'meja', 'pesanan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_meja'           => ['required', 'exists:meja,id_meja'],
            'nama_pelanggan'    => ['required', 'string', 'max:100'],
            'no_hp_pelanggan'   => ['nullable', 'string', 'max:20'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'bukti_bayar'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'items'             => ['required', 'array'],
            'items.*'           => ['nullable', 'integer', 'min:0'],
            'catatan.*'         => ['nullable', 'string', 'max:255'],
        ], [
            'nama_pelanggan.required'    => 'Nama pelanggan wajib diisi.',
            'metode_pembayaran.required' => 'Pilih metode pembayaran.',
            'bukti_bayar.mimes'          => 'Bukti transfer harus berupa gambar JPG, PNG, atau WEBP.',
            'bukti_bayar.max'            => 'Ukuran bukti transfer maksimal 2 MB.',
            'bukti_bayar.uploaded'       => 'Bukti transfer gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ]);

        $items = collect($data['items'])->filter(fn ($qty) => (int) $qty > 0);

        if ($items->isEmpty()) {
            return back()->withErrors(['items' => 'Pilih minimal satu menu.'])->withInput();
        }

        $meja = Meja::findOrFail($data['id_meja']);

        try {
            $kode = DB::transaction(function () use ($data, $items, $meja, $request) {
                $menus = Menu::whereIn('id_menu', $items->keys())->lockForUpdate()->get()->keyBy('id_menu');

                $total = 0;
                foreach ($items as $idMenu => $qty) {
                    $menu = $menus->get($idMenu);
                    if (! $menu) {
                        throw new \RuntimeException('Menu tidak ditemukan.');
                    }
                    if ($menu->stok < $qty) {
                        throw new \RuntimeException("Stok {$menu->nama_menu} tidak cukup (sisa {$menu->stok}).");
                    }
                    $total += $menu->harga * $qty;
                }

                $kode = 'PSN' . now()->format('ymd') . Str::upper(Str::random(6)); // acak, tidak bisa ditebak

                $pesanan = Pesanan::create([
                    'kode_pesanan'      => $kode,
                    'id_meja'           => $meja->id_meja,
                    'no_meja'           => $meja->nomor_meja,
                    'nama_pelanggan'    => $data['nama_pelanggan'],
                    'no_hp_pelanggan'   => $data['no_hp_pelanggan'] ?? null,
                    'total_bayar'       => $total,
                    'status_pesanan'    => 'diterima',
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'status_pembayaran' => 'pending',
                ]);

                foreach ($items as $idMenu => $qty) {
                    $menu = $menus->get($idMenu);

                    DetailPesanan::create([
                        'id_pesanan' => $pesanan->id_pesanan,
                        'no_meja'    => $meja->nomor_meja,
                        'id_menu'    => $idMenu,
                        'jumlah'     => $qty,
                        'subtotal'   => $menu->harga * $qty,
                        'catatan'    => $request->input("catatan.$idMenu"),
                    ]);

                    $menu->decrement('stok', $qty);
                }

                $meja->update(['status' => 'terisi']);

                return $kode;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        // Bukti transfer (opsional, hanya untuk QRIS). Disimpan privat, bukan di folder public.
        if ($data['metode_pembayaran'] === 'qris' && $request->hasFile('bukti_bayar')) {
            $path = $request->file('bukti_bayar')->store('bukti');
            Pesanan::where('kode_pesanan', $kode)->update(['bukti_bayar' => $path]);
        }

        return redirect()->route('order.status', $kode)->with('baru', true);
    }

    // Pelanggan mengunggah (atau mengganti) bukti transfer setelah pesanan dibuat
    public function uploadBukti(Request $request, $kode)
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        if ($pesanan->metode_pembayaran !== 'qris' || $pesanan->status_pembayaran !== 'pending') {
            return back()->withErrors([
                'bukti_bayar' => 'Bukti hanya bisa diunggah untuk pesanan QRIS yang belum diverifikasi kasir.',
            ]);
        }

        $request->validate([
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Pilih foto bukti transfer dulu.',
            'bukti_bayar.mimes'    => 'Bukti transfer harus berupa gambar JPG, PNG, atau WEBP.',
            'bukti_bayar.max'      => 'Ukuran bukti transfer maksimal 2 MB.',
            'bukti_bayar.uploaded' => 'Bukti transfer gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ]);

        if ($pesanan->bukti_bayar) {
            Storage::delete($pesanan->bukti_bayar);
        }

        $path = $request->file('bukti_bayar')->store('bukti');
        $pesanan->update(['bukti_bayar' => $path]);

        return redirect()->route('order.status', $kode)
            ->with('sukses_bukti', 'Bukti transfer terkirim. Kasir akan memeriksa lalu memverifikasi pembayaranmu.');
    }
}
