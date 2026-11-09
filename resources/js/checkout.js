(function () {
    const MENU = App.readJson('data-menu');
    const daftar = document.getElementById('daftar');
    let cart = App.loadCart();
    Object.keys(cart).forEach(id => { if (!MENU[id] || MENU[id].stok <= 0) delete cart[id]; });

    function ubah(id, delta) {
        let q = (cart[id] || 0) + delta;
        q = Math.max(0, Math.min(MENU[id].stok, q));
        if (q === 0) delete cart[id]; else cart[id] = q;
        App.saveCart(cart);
        render();
    }

    function render() {
        daftar.replaceChildren();
        const ids = Object.keys(cart);
        let total = 0, jml = 0;

        if (ids.length === 0) {
            const kosong = App.el('div', 'Keranjang masih kosong. ', 'card');
            const a = App.el('a', 'Pilih menu dulu');
            a.href = daftar.dataset.menuUrl;
            kosong.append(a);
            daftar.append(kosong);
        }

        ids.forEach(id => {
            const m = MENU[id];
            const q = cart[id];
            total += m.harga * q;
            jml += q;

            const baris = App.el('div', undefined, 'card rincian-baris');

            const foto = App.el('div', undefined, 'foto');
            if (m.gambar) {
                const img = document.createElement('img');
                img.src = '/images/' + m.gambar;
                img.alt = m.nama;
                foto.append(img);
            } else {
                foto.textContent = '🍽️';
            }

            const info = App.el('div', undefined, 'nama');
            info.append(App.el('div', m.nama), App.el('div', App.rp(m.harga), 'harga'));

            const box = App.el('div', undefined, 'qtybox');
            box.append(
                App.tombol('−', '', () => ubah(id, -1)),
                App.el('span', q),
                App.tombol('+', 'plus', () => ubah(id, 1))
            );

            baris.append(foto, info, box);
            daftar.append(baris);
        });

        document.getElementById('jmlItem').textContent = jml;
        ['subtotal', 'total', 'totalBar'].forEach(i => {
            document.getElementById(i).textContent = App.rp(total);
        });
    }

    document.querySelectorAll('.chips button').forEach(b => {
        b.addEventListener('click', () => {
            const t = document.getElementById('catatan');
            if (!t.value.toLowerCase().includes(b.dataset.t.toLowerCase())) {
                t.value = (t.value ? t.value + ', ' : '') + b.dataset.t;
            }
        });
    });

    document.getElementById('form').addEventListener('submit', e => {
        const ids = Object.keys(cart);
        if (ids.length === 0) {
            e.preventDefault();
            App.toast('Keranjang masih kosong.');
            return;
        }

        const box = document.getElementById('hidden');
        box.replaceChildren();
        const catatan = document.getElementById('catatan').value.trim();

        function tambah(nama, nilai) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = nama;
            i.value = nilai;
            box.append(i);
        }

        ids.forEach(id => {
            tambah('items[' + id + ']', cart[id]);
            if (catatan) tambah('catatan[' + id + ']', catatan);
        });
    });

    render();
})();