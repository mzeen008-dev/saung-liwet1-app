(function () {
    const cari = document.getElementById('cari');
    const status = document.getElementById('filter-status');
    const baris = Array.from(document.querySelectorAll('.baris-menu'));
    let kategori = 'semua';

    function saring() {
        const kata = cari.value.toLowerCase().trim();
        let tampil = 0;

        baris.forEach(b => {
            const cocokKata = b.dataset.nama.includes(kata) || b.dataset.kategori.toLowerCase().includes(kata);
            const cocokKat = kategori === 'semua' || b.dataset.kategori === kategori;
            const cocokStatus = status.value === 'semua' || b.dataset.status === status.value;
            const ok = cocokKata && cocokKat && cocokStatus;
            b.hidden = !ok;
            if (ok) tampil++;
        });

        document.getElementById('jml-tampil').textContent = tampil;
        document.getElementById('kosong-filter').style.display = (baris.length > 0 && tampil === 0) ? 'block' : 'none';
    }

    document.querySelectorAll('.chips-kat button').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.chips-kat button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            kategori = b.dataset.kat;
            saring();
        });
    });

    cari.addEventListener('input', saring);
    status.addEventListener('change', saring);

    // Konfirmasi sebelum menghapus
    document.querySelectorAll('form[data-konfirmasi]').forEach(f => {
        f.addEventListener('submit', e => {
            if (!confirm(f.dataset.konfirmasi)) e.preventDefault();
        });
    });
})();
