<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        [$dari, $sampai, $hariIni] = $this->rentang($request);

        $dasar = Pengeluaran::whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai);

        $total = (float) (clone $dasar)->sum('jumlah');
        $perKategori = (clone $dasar)
            ->selectRaw('kategori, COUNT(*) AS banyak, SUM(jumlah) AS total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        $daftar = (clone $dasar)
            ->orderByDesc('tanggal')
            ->orderByDesc('id_pengeluaran')
            ->paginate(20)
            ->withQueryString();

        $pencatat = User::pluck('nama', 'id_user');
        $kategori = Pengeluaran::KATEGORI;

        $tanggal = Carbon::parse($hariIni);
        $cepat = [
            'Hari ini'  => [$hariIni, $hariIni],
            '7 hari'    => [$tanggal->copy()->subDays(6)->toDateString(), $hariIni],
            'Bulan ini' => [$tanggal->copy()->startOfMonth()->toDateString(), $hariIni],
        ];

        return view('admin.pengeluaran.index', compact('dari', 'sampai', 'hariIni', 'total', 'perKategori', 'daftar', 'pencatat', 'kategori', 'cepat'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'    => ['required', 'date_format:Y-m-d'],
            'kategori'   => ['required', Rule::in(Pengeluaran::KATEGORI)],
            'keterangan' => ['required', 'string', 'max:255'],
            'jumlah'     => ['required', 'numeric', 'min:1', 'max:99999999'],
        ], [
            'tanggal.required'    => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Tanggal tidak valid.',
            'kategori.in'         => 'Pilih kategori yang tersedia.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'jumlah.required'     => 'Jumlah wajib diisi.',
            'jumlah.numeric'      => 'Jumlah harus berupa angka.',
            'jumlah.min'          => 'Jumlah minimal Rp 1.',
            'jumlah.max'          => 'Jumlah terlalu besar.',
        ]);

        Pengeluaran::create([
            'tanggal'    => $data['tanggal'],
            'kategori'   => $data['kategori'],
            'keterangan' => $data['keterangan'],
            'jumlah'     => $data['jumlah'],
            'id_user'    => $request->user()->id_user,
        ]);

        return back()->with('sukses', 'Pengeluaran Rp ' . number_format((float) $data['jumlah'], 0, ',', '.') . ' berhasil dicatat.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $pengeluaran->delete();

        return back()->with('sukses', 'Catatan pengeluaran dihapus.');
    }

    // Tanggal acuan dari MySQL supaya sama dengan zona waktu data pesanan
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
}