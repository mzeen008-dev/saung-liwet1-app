@extends('layouts.staff')

@section('title', $meja->exists ? 'Ubah Meja' : 'Tambah Meja')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kelola.css') }}">
@endpush

@section('content')
    <h2>{{ $meja->exists ? 'Ubah Meja' : 'Tambah Meja' }}</h2>

    <div class="card form" style="max-width:520px">
        <form action="{{ $meja->exists ? route('admin.meja.update', $meja->id_meja) : route('admin.meja.store') }}" method="POST">
            @csrf
            @if ($meja->exists)
                @method('PUT')
            @endif

            <label for="nomor_meja">Nomor / Nama Meja</label>
            <input id="nomor_meja" type="text" name="nomor_meja" value="{{ old('nomor_meja', $meja->nomor_meja) }}" maxlength="20" placeholder="Contoh: 6 atau VIP-1" required autofocus>
            <div class="petunjuk">Ditampilkan sebagai "Saung ..." di halaman pelanggan.</div>
            @error('nomor_meja') <div class="err">{{ $message }}</div> @enderror

            @if ($meja->exists)
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['kosong', 'terisi'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $meja->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <div class="petunjuk">Status berubah otomatis saat ada pesanan dan saat pesanan selesai. Ubah manual hanya kalau perlu.</div>
                @error('status') <div class="err">{{ $message }}</div> @enderror
            @endif

            <p style="margin-top:20px">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('admin.meja.index') }}" style="margin-left:12px">Batal</a>
            </p>
        </form>
    </div>

    @if ($meja->exists)
        <p class="catatan-bawah">Mengganti nomor meja tidak mengubah nomor pada pesanan lama, supaya riwayat dan laporan tetap sama.</p>
    @endif
@endsection
