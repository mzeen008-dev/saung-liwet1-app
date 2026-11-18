@extends('layouts.staff')

@section('title', 'Pengeluaran')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pengeluaran.css') }}">
@endpush

@section('content')
    @if ($errors->any())
        <div class="alert err">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="card filter" method="GET" action="{{ route('admin.pengeluaran.index') }}">
        <div>
            <label for="dari">Dari</label>
            <input type="date" id="dari" name="dari" value="{{ $dari }}">
        </div>
        <div>
            <label for="sampai">Sampai</label>
            <input type="date" id="sampai" name="sampai" value="{{ $sampai }}">
        </div>
        <button type="submit" class="btn">Terapkan</button>
        <div class="cepat">
            @foreach ($cepat as $nama => [$a, $b])
                <a href="{{ route('admin.pengeluaran.index', ['dari' => $a, 'sampai' => $b]) }}">{{ $nama }}</a>
            @endforeach
        </div>
    </form>

    <div class="kartu-stat">
        <div class="stat-box utama">
            <small>TOTAL PENGELUARAN</small>
            <b>Rp {{ number_format($total, 0, ',', '.') }}</b>
            <span>{{ $dari }} s/d {{ $sampai }}</span>
        </div>
        @foreach ($perKategori as $k)
            <div class="stat-box">
                <small>{{ strtoupper($k->kategori) }}</small>
                <b>Rp {{ number_format($k->total, 0, ',', '.') }}</b>
                <span>{{ $k->banyak }} catatan</span>
            </div>
        @endforeach
    </div>

    <h2>Catat Pengeluaran</h2>
    <form class="card form-pengeluaran" method="POST" action="{{ route('admin.pengeluaran.store') }}">
        @csrf
        <div>
            <label for="f-tanggal">Tanggal</label>
            <input type="date" id="f-tanggal" name="tanggal" value="{{ old('tanggal', $hariIni) }}" required>
        </div>
        <div>
            <label for="f-kategori">Kategori</label>
            <select id="f-kategori" name="kategori" required>
                @foreach ($kategori as $k)
                    <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="lebar">
            <label for="f-ket">Keterangan</label>
            <input type="text" id="f-ket" name="keterangan" maxlength="255" value="{{ old('keterangan') }}" placeholder="Contoh: beli beras 25 kg" required>
        </div>
        <div>
            <label for="f-jumlah">Jumlah (Rp)</label>
            <input type="number" id="f-jumlah" name="jumlah" min="1" max="99999999" step="1" value="{{ old('jumlah') }}" required>
        </div>
        <button type="submit" class="btn">Simpan</button>
    </form>

    <h2>Daftar Pengeluaran</h2>
    <div class="card">
        <table>
            <tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th><th>Dicatat oleh</th><th>Aksi</th></tr>
            @forelse ($daftar as $p)
                <tr>
                    <td>{{ $p->tanggal }}</td>
                    <td>{{ $p->kategori }}</td>
                    <td>{{ $p->keterangan }}</td>
                    <td><b>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</b></td>
                    <td>{{ $pencatat[$p->id_user] ?? '-' }}</td>
                    <td class="aksi-menu">
                        <form action="{{ route('admin.pengeluaran.destroy', $p->id_pengeluaran) }}" method="POST"
                              data-konfirmasi="Hapus catatan pengeluaran ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="kosong">Belum ada pengeluaran pada rentang ini.</td></tr>
            @endforelse
        </table>

        @if ($daftar->hasPages())
            <div class="halaman">
                @if ($daftar->previousPageUrl())
                    <a href="{{ $daftar->previousPageUrl() }}">← Sebelumnya</a>
                @endif
                <span>Halaman {{ $daftar->currentPage() }} dari {{ $daftar->lastPage() }}</span>
                @if ($daftar->nextPageUrl())
                    <a href="{{ $daftar->nextPageUrl() }}">Berikutnya →</a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pengeluaran.js') }}"></script>
@endpush