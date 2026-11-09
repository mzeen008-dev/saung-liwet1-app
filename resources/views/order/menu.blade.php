@extends('layouts.pelanggan')

@section('title', 'Menu')
@section('subjudul', 'Menu')
@section('aktif', 'menu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
@endpush

@section('content')
    @php
        $kategori = $menus->pluck('kategori')->unique();
        $data = $menus->mapWithKeys(fn ($m) => [$m->id_menu => [
            'nama'  => $m->nama_menu,
            'harga' => (float) $m->harga,
            'stok'  => (int) $m->stok,
        ]]);
    @endphp

    <div class="atas">
        <span class="lok">📍 Saung {{ $meja->nomor_meja }} • Meja Lesehan</span>
        <span class="siap">● Dapur Siap Melayani</span>
    </div>

    <input type="search" id="cari" placeholder="🔎 Cari menu, misal: Nasi Liwet...">

    <div class="tabs">
        <button type="button" class="tab on" data-k="semua">Semua</button>
        @foreach ($kategori as $k)
            <button type="button" class="tab" data-k="{{ $k }}">{{ $k }}</button>
        @endforeach
    </div>

    @forelse ($menus as $m)
        <div class="card item" data-id="{{ $m->id_menu }}" data-kategori="{{ $m->kategori }}" data-nama="{{ strtolower($m->nama_menu) }}">
            <div class="baris">
                <div class="info">
                    <span class="label">{{ $m->kategori }}</span>
                    <h3>{{ $m->nama_menu }}</h3>
                    <p>{{ $m->deskripsi }}</p>
                </div>
                <div class="foto">
                    @if (! empty($m->gambar))
                        <img src="{{ asset('images/' . $m->gambar) }}" alt="{{ $m->nama_menu }}">
                    @else
                        🍽️
                    @endif
                </div>
            </div>
            <div class="kaki">
                <div><small>Harga</small><span class="harga">Rp {{ number_format($m->harga, 0, ',', '.') }}</span></div>
                <div class="ctl"></div>
            </div>
        </div>
    @empty
        <div class="card" style="text-align:center">Belum ada menu.</div>
    @endforelse

    <div class="bar" id="bar">
        <div class="ket"><b id="barTotal"></b><br><span id="barNama"></span></div>
        <a href="{{ route('order.checkoutPage', $meja->id_meja) }}">Checkout →</a>
    </div>

    <script type="application/json" id="data-menu">@json($data)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu.js') }}"></script>
@endpush