@extends('layouts.pelanggan')

@section('title', 'Beranda')
@section('subjudul', 'Beranda')
@section('aktif', 'beranda')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/beranda.css') }}">
    <link rel="stylesheet" href="{{ asset('css/foto.css') }}">
@endpush

@section('content')
    <div class="card hero">
        <div class="logo-besar">🍲</div>
        <h2>Saung Liwet</h2>
        <small>MASAKAN SUNDA ASLI</small>
        <div class="tagline">MASAKAN SUNDA TRADISIONAL</div>
        <span class="jam">🕙 Buka Hari Ini: 10:00 - 21:30 WIB</span>
    </div>

    <div class="card sesi">
        <div class="ikon">🏡</div>
        <div>
            <div class="aktif">● SESI MEJA AKTIF</div>
            <b>Saung Lesehan {{ $meja->nomor_meja }}</b>
        </div>
        <span class="vip">VIP Area</span>
    </div>

    <div class="card">
        <b>Sampurasun, Wargi Saung!</b>
        <p style="margin:6px 0 0;color:#444">Selamat datang di Saung Liwet. Silakan jelajahi hidangan khas Tatar Pasundan dan lakukan pesanan langsung dari tempat duduk Anda.</p>
    </div>

    @if ($spesial)
        <a class="banner banner-foto" href="{{ route('order.menu', $meja->id_meja) }}"
           style="background-image: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.72)), url('{{ asset('images/' . $spesial->gambar) }}')">
            <span class="tag">SPESIAL HARI INI</span>
            <b>{{ $spesial->nama_menu }}</b>
            <small>Rp {{ number_format($spesial->harga, 0, ',', '.') }}</small>
        </a>
    @else
        <div class="banner">
            <span class="tag">SPESIAL HARI INI</span> Resep Otentik Garut
            <b>Paket Nasi Liwet Komplit Kastrol</b>
        </div>
    @endif

    <a class="btn btn-oranye" href="{{ route('order.menu', $meja->id_meja) }}">Mulai Pesan Menu →</a>

    <div class="dua">
        <button type="button" class="btn btn-putih" data-panggil>🔔 Panggil Pelayan</button>
        <a class="btn btn-putih" href="#fasilitas">ℹ️ Fasilitas Resto</a>
    </div>

    <div class="card" id="fasilitas" style="background:#eef2ff">
        <div style="display:flex;justify-content:space-between">
            <b>Fasilitas Saung</b><b style="color:var(--hijau);font-size:13px">Tersedia Lengkap</b>
        </div>
        <div class="fas">
            <div>📶<br>Free Wi-Fi<small>Saung_Guest</small></div>
            <div>🕌<br>Musholla<small>Lantai 1 Belakang</small></div>
            <div>🚻<br>Toilet Bersih<small>Area Samping</small></div>
        </div>
    </div>
@endsection
