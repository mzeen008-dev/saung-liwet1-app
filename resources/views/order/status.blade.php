@extends('layouts.pelanggan')

@section('title', 'Status Pesanan')
@section('subjudul', 'Status Dapur')
@section('aktif', 'status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/status.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bukti.css') }}">
    <link rel="stylesheet" href="{{ asset('css/qris.css') }}">
@endpush

@section('content')
    <div class="tabs-status">
        <span class="on">📍 Status Saung (Tamu)</span>
        <a href="{{ route('login') }}">🔒 Panel Dapur &amp; Kasir</a>
    </div>

    @if (session('sukses_bukti'))
        <div class="ok-bukti">{{ session('sukses_bukti') }}</div>
    @endif

    <div id="status-root"
         data-kode="{{ $kode }}"
         data-baru="{{ session('baru') ? '1' : '0' }}"
         data-api="{{ url('/api/pesanan/kode') }}">
        <div id="isi"><div class="card">Memuat...</div></div>
    </div>

    @if ($pesanan->metode_pembayaran === 'qris')
        <div class="card kotak-bukti" id="form-bukti" @if ($pesanan->status_pembayaran !== 'pending') style="display:none" @endif>
            <b>Bayar dengan QRIS</b>
            @include('order._qris', ['nominal' => $pesanan->total_bayar])
            <b style="margin-top:12px;display:block">Bukti Transfer QRIS</b>
            <p id="bukti-status">
                {{ $pesanan->bukti_bayar
                    ? 'Bukti sudah terkirim. Kamu bisa mengunggah ulang kalau ada yang salah.'
                    : 'Setelah membayar lewat QRIS, unggah screenshot bukti transfer agar kasir bisa memverifikasi.' }}
            </p>
            <form action="{{ route('order.bukti', $kode) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="bukti_bayar" accept="image/png,image/jpeg,image/webp" required>
                <button type="submit" class="btn btn-oranye">Kirim Bukti Transfer</button>
            </form>
            <p style="font-size:12px;color:#6b7280">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
        </div>
    @endif

    <button type="button" class="btn btn-coklat" data-panggil>🔔 Panggil Pelayan ke Saung {{ $meja->nomor_meja }}</button>
    <button type="button" class="btn btn-putih" id="btnNota" style="margin-top:12px">🖨️ Minta Nota Fisik &amp; Bill</button>
@endsection

@push('scripts')
    <script src="{{ asset('js/status.js') }}"></script>
@endpush
