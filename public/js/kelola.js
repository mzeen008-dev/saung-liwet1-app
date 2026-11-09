(function () {
    // Konfirmasi sebelum menghapus
    document.querySelectorAll('form[data-konfirmasi]').forEach(f => {
        f.addEventListener('submit', e => {
            if (!confirm(f.dataset.konfirmasi)) e.preventDefault();
        });
    });

    // Salin link meja ke clipboard (dengan cadangan untuk alamat http non-localhost)
    document.querySelectorAll('[data-salin]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const teks = btn.dataset.salin;
            let berhasil = false;

            try {
                await navigator.clipboard.writeText(teks);
                berhasil = true;
            } catch (e) {
                const input = btn.previousElementSibling;
                if (input && input.select) {
                    input.select();
                    try { berhasil = document.execCommand('copy'); } catch (err) { berhasil = false; }
                }
            }

            const awal = btn.textContent;
            btn.textContent = berhasil ? 'Tersalin ✓' : 'Gagal';
            setTimeout(() => { btn.textContent = awal; }, 1500);
        });
    });
})();
