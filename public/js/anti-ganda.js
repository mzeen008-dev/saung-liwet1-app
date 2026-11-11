/*
 * Anti klik ganda (BUG-18, S3-07)
 * Saat form dikirim, tombol submit dinonaktifkan dan teksnya menjadi "Memproses...".
 * Pengiriman kedua pada form yang sama diabaikan.
 * Form yang tidak ingin diproteksi: tambahkan atribut data-tanpa-anti-ganda.
 */
(function () {
    'use strict';

    var TEKS = 'Memproses...';

    function tombolDalam(form) {
        return form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
    }

    function kunci(form) {
        tombolDalam(form).forEach(function (t) {
            if (t.tagName === 'INPUT') {
                t.dataset.teksAsli = t.value;
                t.value = TEKS;
            } else {
                t.dataset.teksAsli = t.textContent;
                t.textContent = TEKS;
            }
            t.disabled = true;
            t.setAttribute('aria-busy', 'true');
        });
    }

    function buka(form) {
        form.dataset.sedangKirim = '0';
        tombolDalam(form).forEach(function (t) {
            if (t.dataset.teksAsli !== undefined) {
                if (t.tagName === 'INPUT') { t.value = t.dataset.teksAsli; }
                else { t.textContent = t.dataset.teksAsli; }
            }
            t.disabled = false;
            t.removeAttribute('aria-busy');
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) { return; }
        if (form.hasAttribute('data-tanpa-anti-ganda')) { return; }

        // Pengiriman kedua (klik ganda): batalkan.
        if (form.dataset.sedangKirim === '1') {
            e.preventDefault();
            return;
        }
        form.dataset.sedangKirim = '1';

        // Tombol dikunci setelah semua handler lain selesai, supaya data form tetap terkirim.
        setTimeout(function () {
            if (e.defaultPrevented) {
                form.dataset.sedangKirim = '0'; // dibatalkan oleh validasi lain, jangan kunci
                return;
            }
            kunci(form);
        }, 0);
    });

    // Tombol Back di browser: kembalikan tombol ke keadaan normal.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form').forEach(buka);
    });
})();
