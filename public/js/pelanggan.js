(function () {
    const d = document.body.dataset;

    const App = {
        idMeja: d.idMeja,
        nomorMeja: d.nomorMeja,
        cartKey: 'cart_meja_' + d.idMeja,

        rp(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        },

        el(tag, teks, kelas) {
            const e = document.createElement(tag);
            if (teks !== undefined) e.textContent = teks;
            if (kelas) e.className = kelas;
            return e;
        },

        tombol(teks, kelas, fn) {
            const b = App.el('button', teks, kelas);
            b.type = 'button';
            b.addEventListener('click', fn);
            return b;
        },

        readJson(id) {
            const e = document.getElementById(id);
            return e ? JSON.parse(e.textContent) : {};
        },

        loadCart() {
            try { return JSON.parse(localStorage.getItem(App.cartKey) || '{}'); }
            catch (e) { return {}; }
        },

        saveCart(cart) {
            localStorage.setItem(App.cartKey, JSON.stringify(cart));
        },

        toast(teks) {
            const t = document.getElementById('toast');
            t.textContent = teks;
            t.style.display = 'block';
            clearTimeout(App._timer);
            App._timer = setTimeout(() => { t.style.display = 'none'; }, 2500);
        },
    };

    window.App = App;

    document.querySelectorAll('[data-panggil]').forEach(b => {
        b.addEventListener('click', () => App.toast('Pelayan dipanggil ke Saung ' + App.nomorMeja));
    });

    const navStatus = document.getElementById('navStatus');
    if (navStatus) {
        navStatus.addEventListener('click', () => {
            const kode = localStorage.getItem('kode_terakhir');
            if (kode) location.href = '/status/' + encodeURIComponent(kode);
            else App.toast('Belum ada pesanan.');
        });
    }
})();
