<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Saung Liwet</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
    $role = $user->role;
@endphp
<div class="shell">
    <aside class="side">
        <div class="brand">
            <div class="logo">🍲</div>
            <div><b>Saung Liwet</b><small>KHAS SUNDA OPERASIONAL</small></div>
        </div>

        <nav class="menu">
            @if (in_array($role, ['kasir', 'admin']))
                <a href="{{ route('kasir.index') }}" class="{{ request()->routeIs('kasir.*') ? 'on' : '' }}"><span>🧾</span>Kasir POS</a>
            @endif
            @if (in_array($role, ['koki', 'admin']))
                <a href="{{ route('koki.index') }}" class="{{ request()->routeIs('koki.*') ? 'on' : '' }}"><span>👨‍🍳</span>KDS Dapur</a>
            @endif
            @if ($role === 'admin')
                @if (Route::has('admin.menu.index'))
                    <a href="{{ route('admin.menu.index') }}" class="{{ request()->routeIs('admin.menu.*') ? 'on' : '' }}"><span>📦</span>Kelola Stok</a>
                @endif
                @if (Route::has('admin.meja.index'))
                    <a href="{{ route('admin.meja.index') }}" class="{{ request()->routeIs('admin.meja.*') ? 'on' : '' }}"><span>🪑</span>Kelola Meja</a>
                @endif
                @if (Route::has('admin.akun.index'))
                    <a href="{{ route('admin.akun.index') }}" class="{{ request()->routeIs('admin.akun.*') ? 'on' : '' }}"><span>👥</span>Kelola Akun</a>
                @endif
                @if (Route::has('admin.laporan'))
                    <a href="{{ route('admin.laporan') }}" class="{{ request()->routeIs('admin.laporan*') ? 'on' : '' }}"><span>📊</span>Laporan</a>
                @endif
            @endif
        </nav>

        <div class="user">
            <div class="avatar">{{ strtoupper(substr($user->nama, 0, 1)) }}</div>
            <div class="info"><b>{{ $user->nama }}</b><small>{{ ucfirst($role) }}</small></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Logout">⎋</button>
            </form>
        </div>
    </aside>

    <div class="konten">
        <header class="topbar">
            <h1>@yield('title')</h1>
            <span class="chip">{{ ucfirst($role) }}</span>
        </header>

        <main>
                        @php
                $bagian = null; $indeks = null; $aksi = null;
                if (request()->routeIs('kasir.*'))        { $bagian = 'Kasir POS';   $indeks = 'kasir.index'; }
                elseif (request()->routeIs('koki.*'))     { $bagian = 'KDS Dapur';   $indeks = 'koki.index'; }
                elseif (request()->routeIs('admin.menu.*')) { $bagian = 'Kelola Stok'; $indeks = 'admin.menu.index'; }
                elseif (request()->routeIs('admin.meja.*')) { $bagian = 'Kelola Meja'; $indeks = 'admin.meja.index'; }
                elseif (request()->routeIs('admin.akun.*')) { $bagian = 'Kelola Akun'; $indeks = 'admin.akun.index'; }
                elseif (request()->routeIs('admin.laporan*')) { $bagian = 'Laporan';   $indeks = 'admin.laporan'; }
                if (request()->routeIs('*.create'))   { $aksi = 'Tambah'; }
                elseif (request()->routeIs('*.edit')) { $aksi = 'Ubah'; }
            @endphp
            <nav class="krumb" aria-label="Jejak halaman">
                <span>{{ ucfirst($role) }}</span>
                @if ($bagian)
                    <span class="sep">&rsaquo;</span>
                    @if ($aksi)
                        <a href="{{ route($indeks) }}">{{ $bagian }}</a>
                        <span class="sep">&rsaquo;</span>
                        <span class="now">{{ $aksi }}</span>
                    @else
                        <span class="now">{{ $bagian }}</span>
                    @endif
                @endif
            </nav>
            @if (session('sukses'))
                <div class="alert ok">{{ session('sukses') }}</div>
            @endif
            @if (session('info'))
                <div class="alert inf">{{ session('info') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
