(function () {
    const lebar = document.body.dataset.lebar;

    // Ingat ukuran kertas pilihan supaya struk berikutnya langsung memakainya
    try { localStorage.setItem('lebar_struk', lebar); } catch (e) { /* abaikan */ }

    const cetak = document.getElementById('cetak');
    if (cetak) cetak.addEventListener('click', () => window.print());

    const tutup = document.getElementById('tutup');
    if (tutup) tutup.addEventListener('click', () => window.close());

    // Dibuka dari Kasir POS: langsung tampilkan dialog cetak
    if (document.body.dataset.otomatis === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    }
})();
