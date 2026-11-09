@extends('layouts.staff')

@section('title', 'KDS Dapur')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kds.css') }}">
@endpush

@section('content')
    <div id="kds"
         data-url="{{ route('koki.data') }}"
         data-update="{{ route('koki.updateStatus', ['id_pesanan' => '__ID__']) }}">

        <div class="kds-atas">
            <div class="kds-judul">
                <h2>Dapur Utama Saung Liwet</h2>
                <small>Layar tiket dapur • diperbarui otomatis tiap 5 detik</small>
            </div>
            <div class="stat"><small>WAKTU DAPUR</small><b id="jam">--:--:--</b></div>
            <div class="stat"><small>BEBAN PESANAN</small><b id="stat-aktif">0 Tiket Aktif</b></div>
            <div class="stat"><small>RATA-RATA TUNGGU</small><b id="stat-rata">0 Menit</b></div>
            <button type="button" id="buzzer" class="buzzer on">🔔 Buzzer ON</button>
        </div>

        <div id="kds-info" class="alert inf" style="display:none"></div>

        <div class="kolom-wrap">
            <section class="kolom k-baru">
                <h3>ANTREAN BARU <span class="hitung" id="n-diterima">0</span></h3>
                <div id="kol-diterima"></div>
            </section>
            <section class="kolom k-masak">
                <h3>SEDANG DIMASAK <span class="hitung" id="n-dimasak">0</span></h3>
                <div id="kol-dimasak"></div>
            </section>
            <section class="kolom k-siap">
                <h3>SIAP SAJI / PANGGILAN WAITER <span class="hitung" id="n-siap">0</span></h3>
                <div id="kol-siap"></div>
            </section>
        </div>

        <p class="kds-kaki">Tiket muncul setelah kasir memverifikasi pembayaran. Tiket berwarna merah berarti sudah menunggu 15 menit atau lebih.</p>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/kds.js') }}"></script>
@endpush
