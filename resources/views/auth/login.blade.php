<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Saung Liwet</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="kartu">
        <section class="kiri">
            <div class="brand">
                <div class="logo">🍲</div>
                <div><b>Saung Liwet</b><small>SISTEM OPERASIONAL KHAS SUNDA</small></div>
            </div>

            <p class="label">PERAN OPERASIONAL</p>
            <ul class="peran">
                <li><b>Admin / Owner</b><span>Kelola menu dan stok</span></li>
                <li><b>Kasir POS</b><span>Verifikasi pembayaran pesanan</span></li>
                <li><b>Koki Dapur</b><span>Pantau dan proses tiket di KDS</span></li>
            </ul>

            <p class="catatan">Hak akses ditentukan oleh akun yang login, bukan oleh pilihan di layar.</p>
        </section>

        <section class="kanan">
            <h2>Masuk ke Sistem</h2>
            <p class="sub">Gunakan akun petugas yang diberikan admin.</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <label for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>

                <div class="baris-label">
                    <label for="password">Password</label>
                    <button type="button" id="tampil" class="tautan">👁 Tampilkan</button>
                </div>
                <input id="password" type="password" name="password" autocomplete="current-password" required>

                <button type="submit" class="masuk">Login ke Sistem →</button>
            </form>

            <p class="bantuan">Lupa password? Hubungi admin.</p>
        </section>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
