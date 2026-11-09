@extends('layouts.staff')

@section('title', 'Kelola Akun')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kelola.css') }}">
@endpush

@section('content')
    <div class="bar-judul">
        <div>
            <h2>Akun Petugas ({{ $akun->count() }})</h2>
            <small>{{ $akun->where('role', 'admin')->count() }} admin •
                {{ $akun->where('role', 'kasir')->count() }} kasir •
                {{ $akun->where('role', 'koki')->count() }} koki</small>
        </div>
        <a class="btn-isi" href="{{ route('admin.akun.create') }}">+ Tambah Akun</a>
    </div>

    <div class="card">
        <table>
            <tr><th>Nama</th><th>Username</th><th>Peran</th><th>Aksi</th></tr>
            @forelse ($akun as $a)
                <tr>
                    <td>
                        <b>{{ $a->nama }}</b>
                        @if ($a->id_user === auth()->id()) <span class="penanda">kamu</span> @endif
                    </td>
                    <td>{{ $a->username }}</td>
                    <td><span class="peran {{ $a->role }}">{{ $a->role }}</span></td>
                    <td class="aksi">
                        <a href="{{ route('admin.akun.edit', $a->id_user) }}">Ubah</a>
                        @if ($a->id_user !== auth()->id())
                            <form action="{{ route('admin.akun.destroy', $a->id_user) }}" method="POST"
                                  data-konfirmasi="Hapus akun {{ $a->username }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hapus">Hapus</button>
                            </form>
                        @endif
                        @if (in_array($a->id_user, $punyaRiwayat))
                            <small class="catatan-kecil">punya riwayat verifikasi</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="kosong">Belum ada akun.</td></tr>
            @endforelse
        </table>
    </div>

    <p class="catatan-bawah">Akun yang pernah memverifikasi pembayaran tidak bisa dihapus agar riwayat tetap utuh. Harus selalu tersisa minimal satu admin.</p>
@endsection

@push('scripts')
    <script src="{{ asset('js/kelola.js') }}"></script>
@endpush
