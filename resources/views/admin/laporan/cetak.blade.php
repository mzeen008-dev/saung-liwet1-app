<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan {{ $dari }} s/d {{ $sampai }}</title>
    <link rel="stylesheet" href="{{ asset('css/laporan-cetak.css') }}">
</head>
<body data-otomatis="{{ $otomatis ? '1' : '0' }}">
    @php
        $fmt = fn ($tgl) => \Carbon\Carbon::parse($tgl)->format('d/m/Y');
        $tunai = $perMetode->get('tunai');
        $qris = $perMetode->get('qris');
        $rata = $jumlah > 0 ? $total / $jumlah : 0;
    @endphp

    <div class="alat">
        <button type="button" id="cetak">🖨 Cetak</button>
        <button type="button" id="tutup" class="garis-btn">Tutup</button>
    </div>

    <main class="kertas">
        <header class="kop">
            <h1>LAPORAN PENDAPATAN</h1>
            <div>Saung Liwet &mdash; Masakan Sunda Asli</div>
            <div>Periode: <b>{{ $fmt($dari) }}</b> s/d <b>{{ $fmt($sampai) }}</b></div>
        </header>

        <h2>1. Ringkasan</h2>
        <table class="ringkas">
            <tbody>
                <tr><td>Total pendapatan</td><td class="kanan">Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
                <tr><td>Jumlah transaksi (lunas)</td><td class="kanan">{{ $jumlah }}</td></tr>
                <tr><td>Rata-rata per transaksi</td><td class="kanan">Rp {{ number_format($rata, 0, ',', '.') }}</td></tr>
                <tr><td>Pendapatan tunai</td><td class="kanan">Rp {{ number_format($tunai->total ?? 0, 0, ',', '.') }} ({{ $tunai->jumlah ?? 0 }} transaksi)</td></tr>
                <tr><td>Pendapatan QRIS</td><td class="kanan">Rp {{ number_format($qris->total ?? 0, 0, ',', '.') }} ({{ $qris->jumlah ?? 0 }} transaksi)</td></tr>
            </tbody>
        </table>

        <h2>2. Pendapatan per Hari</h2>
        <table>
            <thead>
                <tr><th>No</th><th>Tanggal</th><th class="kanan">Transaksi</th><th class="kanan">Pendapatan (Rp)</th></tr>
            </thead>
            <tbody>
                @forelse ($perHari as $i => $h)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $fmt($h->tanggal) }}</td>
                        <td class="kanan">{{ $h->jumlah }}</td>
                        <td class="kanan">{{ number_format($h->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="tengah">Tidak ada pendapatan pada periode ini.</td></tr>
                @endforelse
            </tbody>
            @if ($perHari->isNotEmpty())
                <tfoot>
                    <tr><td colspan="2">Total</td><td class="kanan">{{ $jumlah }}</td><td class="kanan">{{ number_format($total, 0, ',', '.') }}</td></tr>
                </tfoot>
            @endif
        </table>

        <h2>3. Menu Terlaris (10 teratas)</h2>
        <table>
            <thead>
                <tr><th>No</th><th>Menu</th><th>Kategori</th><th class="kanan">Terjual (porsi)</th><th class="kanan">Pendapatan (Rp)</th></tr>
            </thead>
            <tbody>
                @forelse ($terlaris as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $t->nama_menu }}</td>
                        <td>{{ $t->kategori }}</td>
                        <td class="kanan">{{ $t->terjual }}</td>
                        <td class="kanan">{{ number_format($t->pendapatan, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tengah">Belum ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>4. Daftar Transaksi</h2>
        <table class="kecil">
            <thead>
                <tr>
                    <th>No</th><th>Waktu</th><th>Kode</th><th>Meja</th><th>Pelanggan</th>
                    <th>Metode</th><th class="kanan">Total (Rp)</th><th>Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->tanggal_pesan)->format('d/m/Y H:i') }}</td>
                        <td>{{ $p->kode_pesanan }}</td>
                        <td>{{ $p->no_meja }}</td>
                        <td>{{ $p->nama_pelanggan }}</td>
                        <td>{{ strtoupper($p->metode_pembayaran) }}</td>
                        <td class="kanan">{{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                        <td>{{ $kasir[$p->id_user] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="tengah">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
            @if ($transaksi->isNotEmpty())
                <tfoot>
                    <tr><td colspan="6">Total</td><td class="kanan">{{ number_format($transaksi->sum('total_bayar'), 0, ',', '.') }}</td><td></td></tr>
                </tfoot>
            @endif
        </table>

        @if ($terpotong)
            <p class="catatan">Hanya {{ $batas }} transaksi pertama yang dicetak dari {{ $jumlah }} transaksi. Persempit rentang tanggal untuk melihat semuanya.</p>
        @endif

        <footer class="kaki">
            Dicetak oleh <b>{{ $pencetak }}</b> pada {{ \Carbon\Carbon::parse($dicetakPada)->format('d/m/Y H:i') }}.
            Hanya pesanan yang sudah diverifikasi kasir (lunas) yang dihitung.
        </footer>
    </main>

    <script src="{{ asset('js/laporan-cetak.js') }}"></script>
</body>
</html>
