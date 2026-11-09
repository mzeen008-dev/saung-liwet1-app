<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KasirController extends Controller
{
    // Halaman Kasir POS (kerangka saja, datanya diambil lewat data())
    public function index()
    {
        return view('kasir.index');
    }

    // Data untuk Kasir POS (JSON, dipanggil berkala oleh kasir.js)
    public function data()
    {
        $menunggu = Pesanan::with('detail.menu')
            ->where('status_pembayaran', 'pending')
            ->orderBy('tanggal_pesan')
            ->get();

        // "Hari ini" mengikuti tanggal MySQL supaya sama dengan zona waktu data pesanan
        $lunas = Pesanan::with('detail.menu')
            ->where('status_pembayaran', 'lunas')
            ->whereRaw('DATE(tanggal_pesan) = CURDATE()')
            ->orderByDesc('tanggal_pesan')
            ->get();

        $meja = Meja::selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'terisi'), 0) AS terisi")->first();

        return response()->json([
            'menunggu' => $menunggu->map(fn ($p) => $this->peta($p))->values(),
            'lunas'    => $lunas->map(fn ($p) => $this->peta($p))->values(),
            'ringkas'  => [
                'kas_hari_ini' => (float) $lunas->sum('total_bayar'),
                'meja_terisi'  => (int) ($meja->terisi ?? 0),
                'meja_total'   => (int) ($meja->total ?? 0),
            ],
        ]);
    }

    // Menampilkan gambar bukti transfer (hanya untuk kasir dan admin yang login)
    public function bukti($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);

        abort_unless($pesanan->bukti_bayar && Storage::exists($pesanan->bukti_bayar), 404);

        return Storage::response($pesanan->bukti_bayar);
    }

    // Struk untuk printer thermal (58 atau 80 mm). Hanya untuk pesanan yang sudah lunas.
    public function struk(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::with('detail.menu')->findOrFail($id_pesanan);

        abort_unless($pesanan->status_pembayaran === 'lunas', 404);

        $lebar = $request->query('lebar') === '58' ? 58 : 80;
        $otomatis = $request->boolean('cetak');
        $kasir = Auth::user(); // kasir yang sedang login dan mencetak struk ini
        $uang = $pesanan->uang_diterima !== null ? (float) $pesanan->uang_diterima : null;
        $kembalian = $uang !== null ? $uang - (float) $pesanan->total_bayar : null;

        // Perkiraan tinggi kertas (mm) supaya struk tidak dicetak di atas kertas A4
        $perBaris = $lebar === 58 ? 26 : 36;
        $baris = 24 + ($uang !== null ? 2 : 0);
        foreach ($pesanan->detail as $d) {
            $baris += ceil(mb_strlen($d->menu->nama_menu ?? '-') / $perBaris) + 1;
            if ($d->catatan) {
                $baris += ceil((mb_strlen($d->catatan) + 2) / $perBaris);
            }
        }
        $tinggi = (int) ceil($baris * 5.2 + 10);

        return view('kasir.struk', compact('pesanan', 'lebar', 'otomatis', 'kasir', 'uang', 'kembalian', 'tinggi'));
    }

    public function verifikasi(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);

        // Pembayaran tunai: kasir wajib mengisi uang yang diterima, dan harus cukup
        $uang = null;
        if ($pesanan->status_pembayaran === 'pending' && $pesanan->metode_pembayaran === 'tunai') {
            $data = $request->validate([
                'uang_diterima' => ['required', 'numeric', 'min:' . $pesanan->total_bayar, 'max:100000000'],
            ], [
                'uang_diterima.required' => 'Isi jumlah uang yang diterima dari pelanggan.',
                'uang_diterima.numeric'  => 'Uang diterima harus berupa angka.',
                'uang_diterima.min'      => 'Uang yang diterima kurang dari total tagihan.',
                'uang_diterima.max'      => 'Jumlah uang terlalu besar.',
            ]);
            $uang = (float) $data['uang_diterima'];
        }

        $ubah = [
            'status_pembayaran' => 'lunas',
            'id_user'           => Auth::user()->id_user,
        ];
        if ($uang !== null) {
            $ubah['uang_diterima'] = $uang;
        }

        // Update bersyarat: hanya berhasil kalau pesanan masih pending (aman dari klik ganda)
        $berhasil = Pesanan::where('id_pesanan', $id_pesanan)
            ->where('status_pembayaran', 'pending')
            ->update($ubah) > 0;

        if (! $berhasil) {
            $pesan = 'Pesanan ini sudah lunas.';
        } elseif ($uang !== null) {
            $kembalian = $uang - (float) $pesanan->total_bayar;
            $pesan = "Pesanan {$pesanan->kode_pesanan} lunas. Kembalian: Rp " . number_format($kembalian, 0, ',', '.') . '.';
        } else {
            $pesan = "Pesanan {$pesanan->kode_pesanan} dinyatakan lunas dan dikirim ke dapur.";
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesan], $berhasil ? 200 : 409);
        }

        return back()->with($berhasil ? 'sukses' : 'info', $pesan);
    }

    private function peta(Pesanan $p): array
    {
        $adaBukti = ! empty($p->bukti_bayar);
        $uang = $p->uang_diterima !== null ? (float) $p->uang_diterima : null;

        return [
            'id'        => $p->id_pesanan,
            'kode'      => $p->kode_pesanan,
            'meja'      => $p->no_meja,
            'nama'      => $p->nama_pelanggan,
            'hp'        => $p->no_hp_pelanggan,
            'jam'       => substr((string) $p->tanggal_pesan, 11, 5),
            'metode'    => $p->metode_pembayaran,
            'total'     => (float) $p->total_bayar,
            'bayar'     => $p->status_pembayaran,
            'status'    => $p->status_pesanan,
            'uang'      => $uang,
            'kembali'   => $uang !== null ? $uang - (float) $p->total_bayar : null,
            'ada_bukti' => $adaBukti,
            'bukti_url' => $adaBukti ? route('kasir.bukti', $p->id_pesanan) : null,
            'item'      => $p->detail->map(fn ($d) => [
                'nama'     => $d->menu->nama_menu ?? '-',
                'jumlah'   => $d->jumlah,
                'subtotal' => (float) $d->subtotal,
                'catatan'  => $d->catatan,
            ])->values(),
        ];
    }
}
