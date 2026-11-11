<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Saung Liwet</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
    @stack('styles')
</head>
<body data-id-meja="{{ $meja->id_meja }}" data-nomor-meja="{{ $meja->nomor_meja }}">
<div class="app">
    <header>
        <div class="logo">🍲</div>
        <div class="judul"><b>Saung Liwet</b><small>@yield('subjudul')</small></div>
        <span class="badge-meja">Saung {{ $meja->nomor_meja }}</span>
        <button class="lonceng" type="button" data-panggil>🔔</button>
    </header>

    <main>
                @php $jejak = trim($__env->yieldContent('aktif')); @endphp
        <nav class="krumb" aria-label="Jejak halaman">
            @if ($jejak === 'beranda' || $jejak === '')
                <span class="now">Beranda</span>
            @else
                <a href="{{ route('order.index', $meja->id_meja) }}">Beranda</a>
                <span class="sep">&rsaquo;</span>
                @if ($jejak === 'menu')
                    <span class="now">Menu</span>
                @elseif ($jejak === 'pesanan')
                    <a href="{{ route('order.menu', $meja->id_meja) }}">Menu</a>
                    <span class="sep">&rsaquo;</span>
                    <span class="now">Pesanan</span>
                @elseif ($jejak === 'status')
                    <span class="now">Status Pesanan</span>
                @endif
            @endif
        </nav>
        @if ($errors->any())
            <div class="alert err">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</div>

<div class="toast" id="toast"></div>

@php $aktif = trim($__env->yieldContent('aktif')); @endphp
<nav class="bawah">
    <a href="{{ route('order.index', $meja->id_meja) }}" class="{{ $aktif === 'beranda' ? 'on' : '' }}"><span>🏠</span>Beranda</a>
    <a href="{{ route('order.menu', $meja->id_meja) }}" class="{{ $aktif === 'menu' ? 'on' : '' }}"><span>🍴</span>Menu</a>
    <a href="{{ route('order.checkoutPage', $meja->id_meja) }}" class="{{ $aktif === 'pesanan' ? 'on' : '' }}"><span>🧺</span>Pesanan</a>
    <a id="navStatus" class="{{ $aktif === 'status' ? 'on' : '' }}"><span>🍲</span>Status</a>
</nav>

<script src="{{ asset('js/pelanggan.js') }}"></script>
<script src="{{ asset('js/anti-ganda.js') }}"></script>
@stack('scripts')
</body>
</html>
