@extends('layouts.staff')

@section('title', $akun->exists ? 'Ubah Akun' : 'Tambah Akun')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kelola.css') }}">
@endpush

@section('content')
    <h2>{{ $akun->exists ? 'Ubah Akun' : 'Tambah Akun' }}</h2>

    <div class="card form" style="max-width:520px">
        <form action="{{ $akun->exists ? route('admin.akun.update', $akun->id_user) : route('admin.akun.store') }}" method="POST" autocomplete="off">
            @csrf
            @if ($akun->exists)
                @method('PUT')
            @endif

            <label for="nama">Nama Lengkap</label>
            <input id="nama" type="text" name="nama" value="{{ old('nama', $akun->nama) }}" maxlength="100" required>
            @error('nama') <div class="err">{{ $message }}</div> @enderror

            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username', $akun->username) }}" maxlength="50" required>
            <div class="petunjuk">Huruf, angka, titik, garis bawah, atau strip. Dipakai untuk login.</div>
            @error('username') <div class="err">{{ $message }}</div> @enderror

            <label for="role">Peran</label>
            <select id="role" name="role" required>
                @foreach (['admin' => 'Admin (semua akses)', 'kasir' => 'Kasir (verifikasi pembayaran)', 'koki' => 'Koki (layar dapur)'] as $nilai => $nama)
                    <option value="{{ $nilai }}" @selected(old('role', $akun->role ?? 'kasir') === $nilai)>{{ $nama }}</option>
                @endforeach
            </select>
            @error('role') <div class="err">{{ $message }}</div> @enderror

            <label for="password">{{ $akun->exists ? 'Password Baru' : 'Password' }}</label>
            <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" {{ $akun->exists ? '' : 'required' }}>
            <div class="petunjuk">
                {{ $akun->exists ? 'Kosongkan jika tidak ingin mengganti password. ' : '' }}Minimal 8 karakter.
            </div>
            @error('password') <div class="err">{{ $message }}</div> @enderror

            <label for="password_confirmation">Ulangi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" {{ $akun->exists ? '' : 'required' }}>

            <p style="margin-top:20px">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('admin.akun.index') }}" style="margin-left:12px">Batal</a>
            </p>
        </form>
    </div>

    @if ($akun->exists)
        <p class="catatan-bawah">Mengganti password atau peran akan mengeluarkan akun itu dari akses API yang sedang berjalan.</p>
    @endif
@endsection
