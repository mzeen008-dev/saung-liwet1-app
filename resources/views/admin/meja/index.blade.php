@extends('layouts.staff')

@section('title', 'Kelola Meja')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kelola.css') }}">
@endpush

@section('content')
    <div class="bar-judul">
        <div>
            <h2>Daftar Meja ({{ $meja->count() }})</h2>
            <small>{{ $meja->where('status', 'terisi')->count() }} terisi • {{ $meja->where('status', 'kosong')->count() }} kosong</small>
        </div>
        <a class="btn-isi" href="{{ route('admin.meja.create') }}">+ Tambah Meja</a>
    </div>

    <div class="card">
        <table>
            <tr><th>Meja</th><th>Status</th><th>Pesanan Berjalan</th><th>Link Pesan</th><th>Aksi</th></tr>
            @forelse ($meja as $m)
                @php $link = url('/order/' . $m->id_meja); @endphp
                <tr>
                    <td><b>Saung {{ $m->nomor_meja }}</b></td>
                    <td><span class="status-meja {{ $m->status }}">{{ $m->status }}</span></td>
                    <td>{{ $berjalan[$m->id_meja] ?? 0 }}</td>
                    <td>
                        <div class="kotak-link">
                            <input type="text" readonly value="{{ $link }}" aria-label="Link meja {{ $m->nomor_meja }}">
                            <button type="button" data-salin="{{ $link }}">Salin</button>
                            <a href="{{ $link }}" target="_blank" rel="noopener">Buka</a>
                        </div>
                    </td>
                    <td class="aksi">
                        <a href="{{ route('admin.meja.edit', $m->id_meja) }}">Ubah</a>
                        <form action="{{ route('admin.meja.destroy', $m->id_meja) }}" method="POST"
                        data-konfirmasi="Hapus meja {{ $m->nomor_meja }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="kosong">Belum ada meja. Klik "Tambah Meja".</td></tr>
            @endforelse
        </table>
    </div>

    <p class="catatan-bawah">
        Link di atas dipakai untuk membuat kode QR tiap meja. Kalau dibuka dari HP, ganti <b>127.0.0.1</b> dengan alamat IP laptop atau domain server.
        Meja yang sudah punya riwayat pesanan tidak bisa dihapus agar laporan tetap utuh.
    </p>
@endsection

@push('scripts')
    <script src="{{ asset('js/kelola.js') }}"></script>
@endpush
