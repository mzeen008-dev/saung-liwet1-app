@extends('layouts.staff')

@section('title', 'Laporan Pendapatan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
@endpush

@section('content')
    @php
        $tunai = $perMetode->get('tunai');
        $qris  = $perMetode->get('qris');
        $maks  = max(1, (float) $perHari->max('total'));
        $rata  = $jumlah > 0 ? $total / $jumlah : 0;
    @endphp

    <form class="card filter" method="GET" action="{{ route('admin.laporan') }}">
        <div>
            <label for="dari">Dari</label>
            <input type="date" id="dari" name="dari" value="{{ $dari }}">
        </div>
        <div>
            <label for="sampai">Sampai</label>
            <input type="date" id="sampai" name="sampai" value="{{ $sampai }}">
        </div>
        <button type="submit" class="btn">Terapkan</button>
        <div class="cepat">
            @foreach ($cepat as $nama => [$a, $b])
                <a href="{{ route('admin.laporan', ['dari' => $a, 'sampai' => $b]) }}">{{ $nama }}</a>
            @endforeach
        </div>
        <div class="aksi-laporan">
            <a class="btn-garis" href="{{ route('admin.laporan.export', ['dari' => $dari, 'sampai' => $sampai]) }}">⬇ Ekspor CSV</a>
            <a class="btn-garis" href="{{ route('admin.laporan.cetak', ['dari' => $dari, 'sampai' => $sampai, 'cetak' => 1]) }}" target="_blank" rel="noopener">🖨 Cetak</a>
        </div>
    </form>

    <div class="kartu-stat">
        <div class="stat-box utama">
            <small>TOTAL PENDAPATAN</small>
            <b>Rp {{ number_format($total, 0, ',', '.') }}</b>
            <span>{{ $dari }} s/d {{ $sampai }}</span>
        </div>
        <div class="stat-box">
            <small>JUMLAH TRANSAKSI</small>
            <b>{{ $jumlah }}</b>
            <span>pesanan lunas</span>
        </div>
        <div class="stat-box">
            <small>RATA-RATA / TRANSAKSI</small>
            <b>Rp {{ number_format($rata, 0, ',', '.') }}</b>
        </div>
        <div class="stat-box">
            <small>TUNAI</small>
            <b>Rp {{ number_format($tunai->total ?? 0, 0, ',', '.') }}</b>
            <span>{{ $tunai->jumlah ?? 0 }} transaksi</span>
        </div>
        <div class="stat-box">
            <small>QRIS</small>
            <b>Rp {{ number_format($qris->total ?? 0, 0, ',', '.') }}</b>
            <span>{{ $qris->jumlah ?? 0 }} transaksi</span>
        </div>
    </div>

    <h2>Pendapatan per Hari</h2>
    <div class="card">
        <table>
            <tr><th>Tanggal</th><th>Transaksi</th><th>Pendapatan</th><th style="width:40%"></th></tr>
            @forelse ($perHari as $h)
                <tr>
                    <td>{{ $h->tanggal }}</td>
                    <td>{{ $h->jumlah }}</td>
                    <td><b>Rp {{ number_format($h->total, 0, ',', '.') }}</b></td>
                    <td><div class="batang"><div style="width: {{ round($h->total / $maks * 100) }}%"></div></div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="kosong">Belum ada pendapatan pada rentang ini.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Menu Terlaris</h2>
    <div class="card">
        <table>
            <tr><th>#</th><th>Menu</th><th>Kategori</th><th>Terjual</th><th>Pendapatan</th></tr>
            @forelse ($terlaris as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><b>{{ $t->nama_menu }}</b></td>
                    <td>{{ $t->kategori }}</td>
                    <td>{{ $t->terjual }} porsi</td>
                    <td>Rp {{ number_format($t->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="kosong">Belum ada penjualan pada rentang ini.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Daftar Transaksi</h2>
    <div class="card">
        <table>
            <tr>
                <th>Waktu</th><th>Kode</th><th>Meja</th><th>Pelanggan</th>
                <th>Metode</th><th>Total</th><th>Kasir</th><th>Bukti</th>
            </tr>
            @forelse ($transaksi as $p)
                <tr>
                    <td>{{ substr((string) $p->tanggal_pesan, 0, 16) }}</td>
                    <td>{{ $p->kode_pesanan }}</td>
                    <td>{{ $p->no_meja }}</td>
                    <td>{{ $p->nama_pelanggan }}</td>
                    <td>{{ strtoupper($p->metode_pembayaran) }}</td>
                    <td>Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                    <td>{{ $kasir[$p->id_user] ?? '-' }}</td>
                    <td>
                        @if ($p->metode_pembayaran === 'qris' && $p->bukti_bayar)
                            <a href="{{ route('kasir.bukti', $p->id_pesanan) }}" target="_blank">Lihat</a>
                        @elseif ($p->metode_pembayaran === 'qris')
                            <span class="tanpa-bukti">Tidak ada</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="kosong">Belum ada transaksi lunas pada rentang ini.</td></tr>
            @endforelse
        </table>

        @if ($transaksi->hasPages())
            <div class="halaman">
                @if ($transaksi->previousPageUrl())
                    <a href="{{ $transaksi->previousPageUrl() }}">← Sebelumnya</a>
                @endif
                <span>Halaman {{ $transaksi->currentPage() }} dari {{ $transaksi->lastPage() }}</span>
                @if ($transaksi->nextPageUrl())
                    <a href="{{ $transaksi->nextPageUrl() }}">Berikutnya →</a>
                @endif
            </div>
        @endif
    </div>

    <p class="catatan-laporan">Hanya pesanan yang sudah diverifikasi kasir (status lunas) yang dihitung sebagai pendapatan.</p>
@endsection
