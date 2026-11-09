@php $adaQris = file_exists(public_path('images/qris.png')); @endphp

<div class="qris-box">
    @if ($adaQris)
        <img src="{{ asset('images/qris.png') }}" alt="QRIS Saung Liwet">
        @isset($nominal)
            <div class="nominal">Rp {{ number_format($nominal, 0, ',', '.') }}</div>
            <small>Masukkan nominal ini saat membayar.</small>
        @endisset
        <small>Scan dengan GoPay, OVO, DANA, ShopeePay, atau aplikasi m-banking.</small>
        <a class="qris-unduh" href="{{ asset('images/qris.png') }}" download="QRIS-Saung-Liwet.png">⬇ Simpan gambar QRIS</a>
    @else
        <div class="qris-kosong">Kode QRIS belum dipasang. Silakan minta kasir menunjukkan kode QRIS.</div>
    @endif
</div>
