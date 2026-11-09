<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class KokiController extends Controller
{
    private const ALUR = [
        'diterima'       => 'dimasak',
        'dimasak'        => 'siap_disajikan',
        'siap_disajikan' => 'selesai',
    ];

    // Halaman KDS (kerangka saja, datanya diambil lewat data())
    public function index()
    {
        return view('koki.index');
    }

    // Data tiket aktif untuk KDS (JSON, dipanggil berkala oleh kds.js)
    public function data()
    {
        $pesanan = Pesanan::with('detail.menu')
            ->selectRaw('pesanan.*, TIMESTAMPDIFF(MINUTE, tanggal_pesan, NOW()) as menit_tunggu')
            ->where('status_pembayaran', 'lunas')
            ->where('status_pesanan', '!=', 'selesai')
            ->orderBy('tanggal_pesan')
            ->get();

        $tiket = $pesanan->map(fn ($p) => [
            'id'     => $p->id_pesanan,
            'kode'   => $p->kode_pesanan,
            'meja'   => $p->no_meja,
            'nama'   => $p->nama_pelanggan,
            'jam'    => substr((string) $p->tanggal_pesan, 11, 5),
            'menit'  => max(0, (int) $p->menit_tunggu),
            'status' => $p->status_pesanan,
            'item'   => $p->detail->map(fn ($d) => [
                'nama'    => $d->menu->nama_menu ?? '-',
                'jumlah'  => $d->jumlah,
                'catatan' => $d->catatan,
            ])->values(),
        ])->values();

        return response()->json(['tiket' => $tiket]);
    }

    public function updateStatus(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);
        $ok = true;

        if ($pesanan->status_pembayaran !== 'lunas') {
            $ok = false;
            $pesan = 'Pesanan belum dibayar.';
        } elseif (! isset(self::ALUR[$pesanan->status_pesanan])) {
            $ok = false;
            $pesan = 'Pesanan sudah selesai.';
        } else {
            $berikutnya = self::ALUR[$pesanan->status_pesanan];
            $pesanan->update(['status_pesanan' => $berikutnya]);

            // Pesanan selesai: kosongkan meja jika tidak ada pesanan aktif lain di meja itu
            if ($berikutnya === 'selesai') {
                $masihAktif = Pesanan::where('id_meja', $pesanan->id_meja)
                    ->where('status_pesanan', '!=', 'selesai')
                    ->exists();

                if (! $masihAktif) {
                    Meja::where('id_meja', $pesanan->id_meja)->update(['status' => 'kosong']);
                }
            }

            $pesan = "Pesanan {$pesanan->kode_pesanan} sekarang: {$berikutnya}.";
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesan], $ok ? 200 : 409);
        }

        return back()->with($ok ? 'sukses' : 'info', $pesan);
    }
}
