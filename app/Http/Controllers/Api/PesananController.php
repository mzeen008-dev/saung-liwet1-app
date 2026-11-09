<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    private const ALUR = [
        'diterima'       => 'dimasak',
        'dimasak'        => 'siap_disajikan',
        'siap_disajikan' => 'selesai',
    ];

    // Staf: daftar pesanan, bisa difilter ?status_pembayaran=pending&status_pesanan=diterima
    public function index(Request $request)
    {
        $query = Pesanan::with('detail.menu')->orderByDesc('tanggal_pesan');

        foreach (['status_pembayaran', 'status_pesanan', 'id_meja'] as $kolom) {
            if ($request->filled($kolom)) {
                $query->where($kolom, $request->input($kolom));
            }
        }

        return response()->json(['data' => $query->get()]);
    }

    // Publik: pelanggan cek status pesanan lewat kode.
    // Hanya data yang perlu untuk halaman status: tanpa nama, nomor HP, atau id kasir.
    public function showByKode(string $kode)
    {
        $p = Pesanan::with('detail.menu')->where('kode_pesanan', $kode)->firstOrFail();

        return response()->json(['data' => [
            'kode_pesanan'      => $p->kode_pesanan,
            'no_meja'           => $p->no_meja,
            'tanggal_pesan'     => $p->tanggal_pesan,
            'metode_pembayaran' => $p->metode_pembayaran,
            'status_pembayaran' => $p->status_pembayaran,
            'status_pesanan'    => $p->status_pesanan,
            'total_bayar'       => $p->total_bayar,
            'ada_bukti'         => ! empty($p->bukti_bayar),
            'detail'            => $p->detail->map(fn ($d) => [
                'jumlah'   => $d->jumlah,
                'subtotal' => $d->subtotal,
                'catatan'  => $d->catatan,
                'menu'     => ['nama_menu' => $d->menu->nama_menu ?? '-'],
            ])->values(),
        ]]);
    }

    // Publik: checkout pelanggan
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_meja'           => ['required', 'exists:meja,id_meja'],
            'nama_pelanggan'    => ['required', 'string', 'max:100'],
            'no_hp_pelanggan'   => ['nullable', 'string', 'max:20'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.id_menu'   => ['required', 'integer', 'distinct', 'exists:menu,id_menu'],
            'items.*.jumlah'    => ['required', 'integer', 'min:1'],
            'items.*.catatan'   => ['nullable', 'string', 'max:255'],
        ]);

        $meja = Meja::findOrFail($data['id_meja']);

        try {
            $pesanan = DB::transaction(function () use ($data, $meja) {
                $ids = collect($data['items'])->pluck('id_menu');
                $menus = Menu::whereIn('id_menu', $ids)->lockForUpdate()->get()->keyBy('id_menu');

                $total = 0;
                foreach ($data['items'] as $item) {
                    $menu = $menus->get($item['id_menu']);
                    if ($menu->stok < $item['jumlah']) {
                        throw new \RuntimeException("Stok {$menu->nama_menu} tidak cukup (sisa {$menu->stok}).");
                    }
                    $total += $menu->harga * $item['jumlah'];
                }

                $pesanan = Pesanan::create([
                    // Kode acak supaya tidak bisa ditebak orang lain
                    'kode_pesanan'      => 'PSN' . now()->format('ymd') . Str::upper(Str::random(6)),
                    'id_meja'           => $meja->id_meja,
                    'no_meja'           => $meja->nomor_meja,
                    'nama_pelanggan'    => $data['nama_pelanggan'],
                    'no_hp_pelanggan'   => $data['no_hp_pelanggan'] ?? null,
                    'total_bayar'       => $total,
                    'status_pesanan'    => 'diterima',
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'status_pembayaran' => 'pending',
                ]);

                foreach ($data['items'] as $item) {
                    $menu = $menus->get($item['id_menu']);

                    DetailPesanan::create([
                        'id_pesanan' => $pesanan->id_pesanan,
                        'no_meja'    => $meja->nomor_meja,
                        'id_menu'    => $menu->id_menu,
                        'jumlah'     => $item['jumlah'],
                        'subtotal'   => $menu->harga * $item['jumlah'],
                        'catatan'    => $item['catatan'] ?? null,
                    ]);

                    $menu->decrement('stok', $item['jumlah']);
                }

                $meja->update(['status' => 'terisi']);

                return $pesanan;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data'    => $pesanan->fresh('detail.menu'),
        ], 201);
    }

    // Kasir/admin: verifikasi pembayaran (untuk tunai, uang_diterima boleh dikirim dan harus cukup)
    public function verifikasi(Request $request, Pesanan $pesanan)
    {
        $ubah = [
            'status_pembayaran' => 'lunas',
            'id_user'           => $request->user()->id_user,
        ];

        if ($pesanan->metode_pembayaran === 'tunai' && $request->filled('uang_diterima')) {
            $data = $request->validate([
                'uang_diterima' => ['numeric', 'min:' . $pesanan->total_bayar, 'max:100000000'],
            ]);
            $ubah['uang_diterima'] = $data['uang_diterima'];
        }

        // Update bersyarat: hanya berhasil kalau masih pending (aman dari permintaan ganda)
        $berhasil = Pesanan::where('id_pesanan', $pesanan->id_pesanan)
            ->where('status_pembayaran', 'pending')
            ->update($ubah) > 0;

        if (! $berhasil) {
            return response()->json(['message' => 'Pesanan sudah lunas.'], 409);
        }

        return response()->json(['message' => 'Pembayaran diverifikasi.', 'data' => $pesanan->fresh('detail.menu')]);
    }

    // Koki/admin: majukan status pesanan
    public function updateStatus(Pesanan $pesanan)
    {
        if ($pesanan->status_pembayaran !== 'lunas') {
            return response()->json(['message' => 'Pesanan belum dibayar.'], 409);
        }

        $berikutnya = self::ALUR[$pesanan->status_pesanan] ?? null;

        if (! $berikutnya) {
            return response()->json(['message' => 'Pesanan sudah selesai.'], 409);
        }

        $pesanan->update(['status_pesanan' => $berikutnya]);

        if ($berikutnya === 'selesai') {
            $masihAktif = Pesanan::where('id_meja', $pesanan->id_meja)
                ->where('status_pesanan', '!=', 'selesai')
                ->exists();

            if (! $masihAktif) {
                Meja::where('id_meja', $pesanan->id_meja)->update(['status' => 'kosong']);
            }
        }

        return response()->json(['message' => "Status sekarang: {$berikutnya}.", 'data' => $pesanan->fresh('detail.menu')]);
    }
}
