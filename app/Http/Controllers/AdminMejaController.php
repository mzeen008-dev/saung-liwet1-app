<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMejaController extends Controller
{
    public function index()
    {
        $meja = Meja::orderByRaw('CAST(nomor_meja AS UNSIGNED), nomor_meja')->get();

        // Jumlah pesanan yang belum selesai per meja
        $berjalan = Pesanan::where('status_pesanan', '!=', 'selesai')
            ->selectRaw('id_meja, COUNT(*) AS jumlah')
            ->groupBy('id_meja')
            ->pluck('jumlah', 'id_meja');

        return view('admin.meja.index', compact('meja', 'berjalan'));
    }

    public function create()
    {
        return view('admin.meja.form', ['meja' => new Meja()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_meja' => ['required', 'string', 'max:20', 'unique:meja,nomor_meja'],
        ], $this->pesan());

        Meja::create(['nomor_meja' => $data['nomor_meja'], 'status' => 'kosong']);

        return redirect()->route('admin.meja.index')->with('sukses', "Meja {$data['nomor_meja']} berhasil ditambahkan.");
    }

    public function edit(Meja $meja)
    {
        return view('admin.meja.form', compact('meja'));
    }

    public function update(Request $request, Meja $meja)
    {
        $data = $request->validate([
            'nomor_meja' => ['required', 'string', 'max:20',
                             Rule::unique('meja', 'nomor_meja')->ignore($meja->id_meja, 'id_meja')],
            'status'     => ['required', Rule::in(['kosong', 'terisi'])],
        ], $this->pesan());

        // Meja yang masih punya pesanan berjalan tidak boleh dikosongkan secara manual
        $masihAktif = Pesanan::where('id_meja', $meja->id_meja)->where('status_pesanan', '!=', 'selesai')->exists();

        if ($data['status'] === 'kosong' && $masihAktif) {
            return back()->withInput()->with('info', 'Meja ini masih punya pesanan yang belum selesai, jadi belum bisa dikosongkan.');
        }

        $meja->update($data);

        return redirect()->route('admin.meja.index')->with('sukses', "Meja {$meja->nomor_meja} berhasil diperbarui.");
    }

    public function destroy(Meja $meja)
    {
        if (Pesanan::where('id_meja', $meja->id_meja)->exists()) {
            return back()->with('info', "Meja {$meja->nomor_meja} sudah punya riwayat pesanan, jadi tidak bisa dihapus agar laporan tetap utuh.");
        }

        $meja->delete();

        return redirect()->route('admin.meja.index')->with('sukses', 'Meja berhasil dihapus.');
    }

    private function pesan(): array
    {
        return [
            'nomor_meja.required' => 'Nomor meja wajib diisi.',
            'nomor_meja.unique'   => 'Nomor meja itu sudah ada.',
        ];
    }
}
