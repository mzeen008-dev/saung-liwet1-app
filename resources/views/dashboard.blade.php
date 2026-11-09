@extends('layouts.staff')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kelola.css') }}">
@endpush

@section('content')
    <h2>Selamat datang, {{ auth()->user()->nama }}</h2>

    <div class="kisi-menu">
        <a href="{{ route('kasir.index') }}"><span>🧾</span><div>Kasir POS<small>Verifikasi pembayaran</small></div></a>
        <a href="{{ route('koki.index') }}"><span>👨‍🍳</span><div>KDS Dapur<small>Tiket pesanan</small></div></a>
        <a href="{{ route('admin.menu.index') }}"><span>📦</span><div>Admin &amp; Stok<small>Menu dan stok</small></div></a>
        <a href="{{ route('admin.meja.index') }}"><span>🪑</span><div>Kelola Meja<small>Tambah dan ubah meja</small></div></a>
        <a href="{{ route('admin.akun.index') }}"><span>👥</span><div>Kelola Akun<small>Admin, kasir, koki</small></div></a>
        <a href="{{ route('admin.laporan') }}"><span>📊</span><div>Laporan<small>Pendapatan</small></div></a>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Halaman pelanggan</h3>
        <p>Link pesan per meja, bisa dipakai untuk kode QR:</p>
        <ul>
            @foreach (\App\Models\Meja::orderByRaw('CAST(nomor_meja AS UNSIGNED), nomor_meja')->get() as $m)
                <li>Meja {{ $m->nomor_meja }}: <a href="{{ route('order.index', $m->id_meja) }}">{{ url('/order/' . $m->id_meja) }}</a></li>
            @endforeach
        </ul>
        <small>Kelola daftar meja di menu <a href="{{ route('admin.meja.index') }}">Kelola Meja</a>.</small>
    </div>
@endsection
