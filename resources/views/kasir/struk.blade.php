<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $pesanan->kode_pesanan }}</title>
    <style>
        /* Ukuran kertas struk: lebar {{ $lebar }} mm, tinggi mengikuti isi (perkiraan) */
        @page { size: {{ $lebar }}mm {{ $tinggi }}mm; margin: 0; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/struk.css') }}">
</head>
<body class="lebar-{{ $lebar }}" data-lebar="{{ $lebar }}" data-otomatis="{{ $otomatis ? '1' : '0' }}">

    <div class="alat">
        <button type="button" id="cetak">🖨 Cetak</button>
        <a href="{{ route('kasir.struk', ['id_pesanan' => $pesanan->id_pesanan, 'lebar' => 58]) }}" class="{{ $lebar === 58 ? 'on' : '' }}">58 mm</a>
        <a href="{{ route('kasir.struk', ['id_pesanan' => $pesanan->id_pesanan, 'lebar' => 80]) }}" class="{{ $lebar === 80 ? 'on' : '' }}">80 mm</a>
        <button type="button" id="tutup">Tutup</button>
    </div>

    <main class="struk">
        <header class="kepala">
            <b>SAUNG LIWET</b>
            <span>Masakan Sunda Asli</span>
        </header>

        <div class="garis"></div>

        <div class="baris"><span>No. Pesanan</span><span class="nilai">{{ $pesanan->kode_pesanan }}</span></div>
        <div class="baris"><span>Waktu</span><span class="nilai">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesan)->format('d/m/Y H:i') }}</span></div>
        <div class="baris"><span>Meja</span><span class="nilai">{{ $pesanan->no_meja }}</span></div>
        <div class="baris"><span>Pemesan</span><span class="nilai">{{ $pesanan->nama_pelanggan }}</span></div>
        <div class="baris"><span>Kasir</span><span class="nilai">{{ $kasir->nama ?? '-' }}</span></div>

        <div class="garis"></div>

        @foreach ($pesanan->detail as $d)
            <div class="item">
                <div class="nm">{{ $d->menu->nama_menu ?? '-' }}</div>
                <div class="baris">
                    <span>{{ $d->jumlah }} x {{ number_format($d->subtotal / max(1, $d->jumlah), 0, ',', '.') }}</span>
                    <span class="nilai">{{ number_format($d->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($d->catatan)
                    <div class="cat">* {{ $d->catatan }}</div>
                @endif
            </div>
        @endforeach

        <div class="garis"></div>

        <div class="baris total"><span>TOTAL BAYAR</span><span class="nilai">Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</span></div>

        <div class="garis"></div>

        <div class="baris"><span>Pembayaran</span><span class="nilai">{{ strtoupper($pesanan->metode_pembayaran) }}</span></div>
        @if ($uang !== null)
            <div class="baris"><span>Bayar</span><span class="nilai">{{ number_format($uang, 0, ',', '.') }}</span></div>
            <div class="baris tebal"><span>Kembali</span><span class="nilai">{{ number_format($kembalian, 0, ',', '.') }}</span></div>
        @endif
        <div class="baris"><span>Status</span><span class="nilai">LUNAS</span></div>

        <div class="garis"></div>

        <footer class="kaki">
            <div>Terima kasih atas kunjungan Anda</div>
            <div>Nuhun, sing wilujeng!</div>
        </footer>
    </main>

    <script src="{{ asset('js/struk.js') }}"></script>
</body>
</html>
