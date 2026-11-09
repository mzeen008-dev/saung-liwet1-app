(function () {
    const cetak = document.getElementById('cetak');
    if (cetak) cetak.addEventListener('click', () => window.print());

    const tutup = document.getElementById('tutup');
    if (tutup) tutup.addEventListener('click', () => window.close());

    // Dibuka dari tombol Cetak di halaman Laporan: langsung tampilkan dialog cetak
    if (document.body.dataset.otomatis === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    }
})();
