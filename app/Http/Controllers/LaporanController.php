<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    // Batas jumlah transaksi yang dicetak sekaligus (supaya halaman cetak tidak terlalu berat)
    private const BATAS_CETAK = 3000;

    public function index(Request $request)
    {
        [$dari, $sampai, $hariIni] = $this->rentang($request);

        $transaksi = $this->dasar($dari, $sampai)
            ->orderByDesc('tanggal_pesan')
            ->paginate(20)
            ->withQueryString();

        $kasir = User::pluck('nama', 'id_user');

        $tanggal = Carbon::parse($hariIni);
        $cepat = [
            'Hari ini'  => [$hariIni, $hariIni],
            '7 hari'    => [$tanggal->copy()->subDays(6)->toDateString(), $hariIni],
            'Bulan ini' => [$tanggal->copy()->startOfMonth()->toDateString(), $hariIni],
        ];

        return view('admin.laporan.index', array_merge(
            $this->ringkasan($dari, $sampai),
            compact('dari', 'sampai', 'transaksi', 'kasir', 'cepat')
        ));
    }

    // Halaman cetak: hanya tabel, semua transaksi pada rentang yang dipilih (tanpa pembagian halaman)
    public function cetak(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $ringkasan = $this->ringkasan($dari, $sampai);
        $transaksi = $this->dasar($dari, $sampai)
            ->orderBy('tanggal_pesan')
            ->limit(self::BATAS_CETAK)
            ->get();

        $kasir = User::pluck('nama', 'id_user');
        $terpotong = $ringkasan['jumlah'] > self::BATAS_CETAK;
        $batas = self::BATAS_CETAK;
        $otomatis = $request->boolean('cetak');
        $pencetak = Auth::user()->nama;
        $dicetakPada = DB::selectOne('SELECT NOW() AS n')->n; // jam sama dengan data pesanan

        return view('admin.laporan.cetak', array_merge(
            $ringkasan,
            compact('dari', 'sampai', 'transaksi', 'kasir', 'terpotong', 'batas', 'otomatis', 'pencetak', 'dicetakPada')
        ));
    }

    public function export(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
        $kasir = User::pluck('nama', 'id_user');
        $namaFile = "laporan-pendapatan-{$dari}_sampai_{$sampai}.csv";

        return response()->streamDownload(function () use ($dari, $sampai, $kasir) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // supaya Excel membaca UTF-8 dengan benar

            fputcsv($out, ['Kode Pesanan', 'Waktu', 'Meja', 'Pelanggan', 'Metode', 'Total Bayar', 'Kasir'], ',', '"', '\\');

            $this->dasar($dari, $sampai)->chunkById(200, function ($baris) use ($out, $kasir) {
                foreach ($baris as $p) {
                    fputcsv($out, [
                        $this->aman($p->kode_pesanan),
                        $p->tanggal_pesan,
                        $this->aman($p->no_meja),
                        $this->aman($p->nama_pelanggan),
                        strtoupper($p->metode_pembayaran),
                        $p->total_bayar,
                        $this->aman($kasir[$p->id_user] ?? '-'),
                    ], ',', '"', '\\');
                }
            }, 'id_pesanan');

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Ringkasan angka, pendapatan per hari, dan menu terlaris untuk satu rentang tanggal
    private function ringkasan(string $dari, string $sampai): array
    {
        $dasar = $this->dasar($dari, $sampai);

        return [
            'total'  => (float) (clone $dasar)->sum('total_bayar'),
            'jumlah' => (clone $dasar)->count(),
            'perMetode' => (clone $dasar)
                ->selectRaw('metode_pembayaran, COUNT(*) AS jumlah, SUM(total_bayar) AS total')
                ->groupBy('metode_pembayaran')
                ->get()
                ->keyBy('metode_pembayaran'),
            'perHari' => (clone $dasar)
                ->selectRaw('DATE(tanggal_pesan) AS tanggal, COUNT(*) AS jumlah, SUM(total_bayar) AS total')
                ->groupBy(DB::raw('DATE(tanggal_pesan)'))
                ->orderByDesc('tanggal')
                ->get(),
            'terlaris' => $this->terlaris($dari, $sampai),
        ];
    }

    // 10 menu paling banyak terjual (hanya dari pesanan lunas)
    private function terlaris(string $dari, string $sampai)
    {
        return DB::table('detail_pesanan')
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'detail_pesanan.id_pesanan')
            ->join('menu', 'menu.id_menu', '=', 'detail_pesanan.id_menu')
            ->where('pesanan.status_pembayaran', 'lunas')
            ->whereDate('pesanan.tanggal_pesan', '>=', $dari)
            ->whereDate('pesanan.tanggal_pesan', '<=', $sampai)
            ->selectRaw('menu.id_menu, menu.nama_menu, menu.kategori, SUM(detail_pesanan.jumlah) AS terjual, SUM(detail_pesanan.subtotal) AS pendapatan')
            ->groupBy('menu.id_menu', 'menu.nama_menu', 'menu.kategori')
            ->orderByDesc('terjual')
            ->orderByDesc('pendapatan')
            ->limit(10)
            ->get();
    }

    // Hanya pesanan yang sudah diverifikasi kasir (lunas) yang dihitung sebagai pendapatan
    private function dasar(string $dari, string $sampai)
    {
        return Pesanan::where('status_pembayaran', 'lunas')
            ->whereDate('tanggal_pesan', '>=', $dari)
            ->whereDate('tanggal_pesan', '<=', $sampai);
    }

    // Tanggal acuan diambil dari MySQL supaya sama dengan zona waktu data pesanan
    private function rentang(Request $request): array
    {
        $hariIni = DB::selectOne('SELECT CURDATE() AS d')->d;
        $valid = fn ($v) => is_string($v)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)
            && checkdate((int) substr($v, 5, 2), (int) substr($v, 8, 2), (int) substr($v, 0, 4));

        $dari   = $valid($request->query('dari')) ? $request->query('dari') : Carbon::parse($hariIni)->startOfMonth()->toDateString();
        $sampai = $valid($request->query('sampai')) ? $request->query('sampai') : $hariIni;

        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai, $hariIni];
    }

    // Cegah "CSV injection": sel yang diawali = + - @ akan dibaca Excel sebagai rumus
    private function aman($nilai): string
    {
        $nilai = (string) $nilai;

        return preg_match('/^[=+\-@\t\r]/', $nilai) ? "'" . $nilai : $nilai;
    }
}
