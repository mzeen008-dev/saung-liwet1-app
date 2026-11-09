@extends('layouts.staff')

@section('title', $menu->exists ? 'Ubah Menu' : 'Tambah Menu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/foto.css') }}">
@endpush

@section('content')
    <h2>{{ $menu->exists ? 'Ubah Menu' : 'Tambah Menu' }}</h2>

    <div class="card form" style="max-width:520px">
        <form action="{{ $menu->exists ? route('admin.menu.update', $menu->id_menu) : route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($menu->exists)
                @method('PUT')
            @endif

            <label>Kategori</label>
            <input type="text" name="kategori" value="{{ old('kategori', $menu->kategori) }}" placeholder="Makanan / Minuman" required>
            @error('kategori') <div class="err">{{ $message }}</div> @enderror

            <label>Nama Menu</label>
            <input type="text" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" required>
            @error('nama_menu') <div class="err">{{ $message }}</div> @enderror

            <label>Harga (Rp)</label>
            <input type="number" name="harga" value="{{ old('harga', $menu->harga ? (int) $menu->harga : '') }}" min="0" required>
            @error('harga') <div class="err">{{ $message }}</div> @enderror

            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3">{{ old('deskripsi', $menu->deskripsi) }}</textarea>

            <label>Stok</label>
            <input type="number" name="stok" value="{{ old('stok', $menu->stok ?? 0) }}" min="0" required>
            @error('stok') <div class="err">{{ $message }}</div> @enderror

            <label for="foto">Foto Menu (opsional)</label>
            @if ($menu->gambar)
                <div class="foto-sekarang">
                    <img src="{{ asset('images/' . $menu->gambar) }}" alt="Foto {{ $menu->nama_menu }}">
                    <label class="cek-hapus"><input type="checkbox" name="hapus_foto" value="1"> Hapus foto ini</label>
                </div>
            @endif
            <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp">
            <div class="petunjuk-foto">
                JPG, PNG, atau WEBP, maksimal 4 MB. Foto dikecilkan otomatis. Pakai foto landscape atau persegi supaya rapi.
                {{ $menu->gambar ? 'Memilih foto baru akan mengganti foto yang sekarang.' : '' }}
            </div>
            <div id="foto-galat" class="err" style="display:none"></div>
            @error('foto') <div class="err">{{ $message }}</div> @enderror
            <img id="pratinjau" class="pratinjau" alt="Pratinjau foto" hidden>

            <p style="margin-top:20px">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('admin.menu.index') }}" style="margin-left:12px">Batal</a>
            </p>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/foto-menu.js') }}"></script>
@endpush
