@extends('layouts.staff')

@section('title', 'Kasir POS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
@endpush

@section('content')
    <div id="pos"
         data-url="{{ route('kasir.data') }}"
         data-verifikasi="{{ route('kasir.verifikasi', ['id_pesanan' => '__ID__']) }}"
         data-struk="{{ route('kasir.struk', ['id_pesanan' => '__ID__']) }}">

        <div class="pos-atas">
            <div class="stat"><small>KASIR BERTUGAS</small><b>{{ auth()->user()->nama }}</b></div>
            <div class="stat"><small>TOTAL KAS MASUK HARI INI</small><b id="st-kas">Rp 0</b></div>
            <div class="stat"><small>OKUPANSI SAUNG</small><b id="st-okupansi">-</b></div>
            <div class="tab-pos">
                <button type="button" data-tab="semua" class="on">Semua <span id="n-semua">0</span></button>
                <button type="button" data-tab="menunggu">Menunggu Verifikasi <span id="n-menunggu">0</span></button>
                <button type="button" data-tab="lunas">Sudah Bayar <span id="n-lunas">0</span></button>
            </div>
        </div>

        <div id="pos-info" class="alert inf" style="display:none"></div>

        <div class="pos-grid">
            <section class="daftar">
                <h3>Pesanan Aktif di Saung</h3>
                <div id="daftar"></div>
            </section>

            <aside class="panel" id="panel"></aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/kasir.js') }}"></script>
@endpush
