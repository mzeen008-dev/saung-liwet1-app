(function () {
    const MENU = App.readJson('data-menu');
    let cart = App.loadCart();
    Object.keys(cart).forEach(id => { if (!MENU[id]) delete cart[id]; });
    let kategoriAktif = 'semua';

    function ubah(id, delta) {
        let q = (cart[id] || 0) + delta;
        q = Math.max(0, Math.min(MENU[id].stok, q));
        if (q === 0) delete cart[id]; else cart[id] = q;
        App.saveCart(cart);
        render();
    }

    function render() {
        document.querySelectorAll('.item').forEach(item => {
            const id = item.dataset.id;
            const ctl = item.querySelector('.ctl');
            const q = cart[id] || 0;
            const m = MENU[id];
            ctl.replaceChildren();

            if (m.stok <= 0) {
                ctl.append(App.el('span', 'Habis', 'habis'));
            } else if (q === 0) {
                ctl.append(App.tombol('+ Tambah', 'tambah', () => ubah(id, 1)));
            } else {
                const box = App.el('div', undefined, 'qtybox');
                box.append(
                    App.tombol('−', '', () => ubah(id, -1)),
                    App.el('span', q),
                    App.tombol('+', 'plus', () => ubah(id, 1))
                );
                ctl.append(box);
            }
        });

        const ids = Object.keys(cart);
        const bar = document.getElementById('bar');
        if (ids.length === 0) { bar.style.display = 'none'; return; }

        let jml = 0, total = 0;
        ids.forEach(id => { jml += cart[id]; total += cart[id] * MENU[id].harga; });
        document.getElementById('barTotal').textContent = jml + ' Item • ' + App.rp(total);
        document.getElementById('barNama').textContent = MENU[ids[ids.length - 1]].nama + ' terpilih';
        bar.style.display = 'flex';
    }

    function saring() {
        const kata = document.getElementById('cari').value.toLowerCase().trim();
        document.querySelectorAll('.item').forEach(item => {
            const cocokKategori = kategoriAktif === 'semua' || item.dataset.kategori === kategoriAktif;
            const cocokNama = item.dataset.nama.includes(kata);
            item.style.display = (cocokKategori && cocokNama) ? '' : 'none';
        });
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('on'));
            tab.classList.add('on');
            kategoriAktif = tab.dataset.k;
            saring();
        });
    });

    document.getElementById('cari').addEventListener('input', saring);

    render();
})();