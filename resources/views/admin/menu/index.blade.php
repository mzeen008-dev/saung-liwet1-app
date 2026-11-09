@extends('layouts.staff')

@section('title', 'Admin & Stok')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/foto.css') }}">
@endpush

@section('content')
    @if ($habis > 0 || $menipis->isNotEmpty())
        <div class="peringatan">
            <b>⚠ Perhatian stok</b>
            @if ($habis > 0)
                <span><b>{{ $habis }}</b> menu habis.</span>
            @endif
            @if ($menipis->isNotEmpty())
                <span>Menipis (sisa {{ $batas }} atau kurang):
                    {{ $menipis->map(fn ($m) => $m->nama_menu . ' (' . $m->stok . ')')->implode(', ') }}.</span>
            @endif
        </div>
    @endif

    @php
        $persenTersedia = $menus->count() > 0 ? round($tersedia / $menus->count() * 100) : 0;
        $persenMeja = $meja->total > 0 ? round($meja->terisi / $meja->total * 100) : 0;
    @endphp

    <div class="kartu-admin">
        <div class="kotak">
            <small>TOTAL MENU</small>
            <b>{{ $menus->count() }}</b>
            <span>{{ $tersedia }} tersedia ({{ $persenTersedia }}%)</span>
        </div>
        <div class="kotak">
            <small>STOK MENIPIS / HABIS</small>
            <b><span class="warna-oranye">{{ $menipis->count() }}</span> / <span class="warna-merah">{{ $habis }}</span></b>
            <span>menu perlu diisi ulang</span>
        </div>
        <div class="kotak">
            <small>PENJUALAN HARI INI</small>
            <b>Rp {{ number_format($penjualan, 0, ',', '.') }}</b>
            <span>{{ $transaksi }} transaksi lunas • <a href="{{ route('admin.laporan') }}">lihat laporan</a></span>
        </div>
        <div class="kotak">
            <small>OKUPANSI SAUNG</small>
            <b>{{ $meja->terisi }} <em>/ {{ $meja->total }} meja</em></b>
            <span>{{ $persenMeja }}% terisi</span>
        </div>
    </div>

    <div class="alat">
        <input type="search" id="cari" placeholder="🔎 Cari menu (nama atau kategori)...">
        <a class="btn-garis" href="{{ route('admin.menu.export') }}">⬇ Ekspor CSV</a>
        <a class="btn-isi" href="{{ route('admin.menu.create') }}">+ Tambah Menu Baru</a>
    </div>

    <div class="filter-baris">
        <div class="chips-kat">
            <button type="button" class="on" data-kat="semua">Semua Kategori ({{ $menus->count() }})</button>
            @foreach ($kategori as $k)
                <button type="button" data-kat="{{ $k }}">{{ $k }} ({{ $menus->where('kategori', $k)->count() }})</button>
            @endforeach
        </div>
        <select id="filter-status">
            <option value="semua">Semua Status Stok</option>
            <option value="tersedia">Tersedia</option>
            <option value="menipis">Menipis</option>
            <option value="habis">Habis</option>
        </select>
    </div>

    <div class="card tabel-menu">
        <table>
            <tr>
                <th>Menu &amp; Kategori</th>
                <th>Harga Satuan</th>
                <th>Sisa Porsi</th>
                <th>Ubah Stok</th>
                <th>Aksi</th>
            </tr>
            @forelse ($menus as $m)
                @php
                    $status = $m->stok <= 0 ? 'habis' : ($m->stok <= $batas ? 'menipis' : 'tersedia');
                    $lebar = min(100, $m->stok * 2);
                @endphp
                <tr class="baris-menu" data-nama="{{ mb_strtolower($m->nama_menu) }}" data-kategori="{{ $m->kategori }}" data-status="{{ $status }}">
                    <td>
                        <div class="sel-menu">
                            @if ($m->gambar)
                                <img class="foto-kecil" src="{{ asset('images/' . $m->gambar) }}" alt="{{ $m->nama_menu }}" loading="lazy">
                            @else
                                <span class="avatar-menu">{{ mb_strtoupper(mb_substr($m->nama_menu, 0, 1)) }}</span>
                            @endif
                            <div>
                                <b>{{ $m->nama_menu }}</b>
                                <small>{{ $m->kategori }}</small>
                            </div>
                        </div>
                    </td>
                    <td><b>Rp {{ number_format($m->harga, 0, ',', '.') }}</b></td>
                    <td>
                        <div class="stok-info">
                            <b>{{ $m->stok }}</b> porsi
                            <span class="label-stok {{ $status }}">{{ $status }}</span>
                        </div>
                        <div class="batang-stok"><div class="{{ $status }}" style="width: {{ $lebar }}%"></div></div>
                    </td>
                    <td>
                        <form class="form-stok" action="{{ route('admin.menu.stok', $m->id_menu) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="stok" min="0" max="100000" value="{{ $m->stok }}">
                            <button type="submit">Simpan</button>
                        </form>
                    </td>
                    <td class="aksi-menu">
                        <a href="{{ route('admin.menu.edit', $m->id_menu) }}">Ubah</a>
                        <form action="{{ route('admin.menu.destroy', $m->id_menu) }}" method="POST"
                              data-konfirmasi="Hapus menu {{ $m->nama_menu }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="kosong">Belum ada menu. Klik "Tambah Menu Baru".</td></tr>
            @endforelse
        </table>
        <div id="kosong-filter" class="kosong" style="display:none">Tidak ada menu yang cocok dengan pencarian.</div>
        <div class="ket-tabel">Menampilkan <span id="jml-tampil">{{ $menus->count() }}</span> dari {{ $menus->count() }} menu</div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin.js') }}"></script>
@endpush
