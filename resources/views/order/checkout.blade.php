@extends('layouts.pelanggan')

@section('title', 'Checkout')
@section('subjudul', 'Pesanan')
@section('aktif', 'pesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bukti.css') }}">
    <link rel="stylesheet" href="{{ asset('css/qris.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catatan-item.css') }}">
@endpush

@section('content')
    @php
        $data = $menus->mapWithKeys(fn ($m) => [$m->id_menu => [
            'nama'     => $m->nama_menu,
            'kategori' => $m->kategori,
            'harga'  => (float) $m->harga,
            'stok'   => (int) $m->stok,
            'gambar' => $m->gambar ?? null,
        ]]);
    @endphp

    <form id="form" action="{{ route('order.checkout') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id_meja" value="{{ $meja->id_meja }}">
        <div id="hidden"></div>

        <div class="card lokasi">
            <div class="ikon">🏡</div>
            <div><small>LOKASI MEJA</small><br><b>Saung {{ $meja->nomor_meja }} (Lesehan)</b></div>
            <span class="ok">Terverifikasi</span>
        </div>

        <h4>👤 Nama Pemesan <span style="color:#c0392b">*</span></h4>
        <input class="isian" type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" placeholder="Contoh: Bpk. Hendra" required maxlength="100">
        <div class="petunjuk">Nama dicantumkan pada nota dan dipanggil saat penyajian.</div>

        <div class="judul-baris">
            <h4>🍽️ Rincian Menu (<span id="jmlItem">0</span> Item)</h4>
            <a href="{{ route('order.menu', $meja->id_meja) }}">Edit Pesanan</a>
        </div>
        <div id="daftar" data-menu-url="{{ route('order.menu', $meja->id_meja) }}"></div>
        <div class="petunjuk">📝 Tulis catatan untuk dapur langsung di kartu menunya (misal: sambal dipisah). Catatan hanya berlaku untuk menu itu.</div>

        <h4>💵 Metode Pembayaran</h4>
        <label class="card bayar">
            <input type="radio" name="metode_pembayaran" value="tunai" {{ old('metode_pembayaran', 'tunai') === 'tunai' ? 'checked' : '' }}>
            <b>Tunai di Kasir</b>
            <div class="ket">Bayar di kasir terlebih dahulu. Pesanan diproses dapur setelah kasir memverifikasi pembayaranmu.</div>
        </label>
        <label class="card bayar">
            <input type="radio" name="metode_pembayaran" value="qris" {{ old('metode_pembayaran') === 'qris' ? 'checked' : '' }}>
            <b>QRIS / Transfer Bank</b>
            <div class="ket">Pesanan diproses dapur setelah kasir memeriksa bukti transfermu.</div>
        </label>

        <div class="upload-bukti">
            <b>Bayar dengan QRIS</b>
            @include('order._qris')
            <div class="petunjuk">Masukkan nominal sesuai <b>Total Pembayaran</b> di bawah.</div>
            <b style="margin-top:12px">Bukti Transfer QRIS (opsional)</b>
            Setelah membayar, unggah screenshot buktinya agar kasir bisa memeriksa. Kamu juga bisa mengunggahnya nanti di halaman status pesanan.
            <input type="file" name="bukti_bayar" accept="image/png,image/jpeg,image/webp">
            <div class="petunjuk">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
        </div>

        <div class="card ringkas">
            <b>Ringkasan Biaya</b>
            <div><span>Subtotal Pesanan</span><b id="subtotal">Rp 0</b></div>
            <div><span>Biaya Layanan Saung</span><b class="gratis">Gratis</b></div>
            <div><b>Total Pembayaran</b><span class="total" id="total">Rp 0</span></div>
        </div>

        <div class="bayar-bar">
            <div><small>TOTAL AKHIR</small><b id="totalBar">Rp 0</b></div>
            <button type="submit" class="btn btn-coklat">KONFIRMASI &amp; PESAN SEKARANG →</button>
        </div>
    </form>

    <script type="application/json" id="data-menu">@json($data)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/checkout.js') }}"></script>
@endpush
