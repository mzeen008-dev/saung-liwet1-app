<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('kode') - Saung Liwet</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/error.css') }}">
</head>
<body>
    <div class="kotak">
        <div class="logo">🍲</div>
        <div class="kode">@yield('kode')</div>
        <h1>@yield('judul')</h1>
        <p>@yield('pesan')</p>
        <div class="aksi">
            <a class="utama" href="{{ url('/') }}">Ke Beranda</a>
            <a class="kedua" href="{{ url('/login') }}">Login Petugas</a>
        </div>
    </div>
</body>
</html>
