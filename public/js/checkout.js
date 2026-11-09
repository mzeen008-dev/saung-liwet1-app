(function () {
    const MENU = App.readJson('data-menu');
    const daftar = document.getElementById('daftar');
    // Nama kunci diawali "cart_meja_" supaya ikut terhapus otomatis setelah pesanan berhasil dibuat
    const KUNCI_CATATAN = 'cart_meja_' + App.idMeja + '_catatan';
    // Saran catatan cepat disesuaikan dengan jenis menu
    const SARAN_MAKANAN = ['Sambal dipisah', 'Nasi hangat', 'Kurangi pedas'];
    const SARAN_MINUMAN = ['Kurangi es', 'Tanpa es', 'Kurangi gula', 'Tanpa gula'];

    function saranUntuk(id) {
        return /minum/i.test(MENU[id].kategori || '') ? SARAN_MINUMAN : SARAN_MAKANAN;
    }

    let cart = App.loadCart();
    Object.keys(cart).forEach(id => { if (!MENU[id] || MENU[id].stok <= 0) delete cart[id]; });

    let catatan = {};
    try { catatan = JSON.parse(localStorage.getItem(KUNCI_CATATAN) || '{}'); } catch (e) { catatan = {}; }

    function simpanCatatan() {
        localStorage.setItem(KUNCI_CATATAN, JSON.stringify(catatan));
    }

    function ubah(id, delta) {
        let q = (cart[id] || 0) + delta;
        q = Math.max(0, Math.min(MENU[id].stok, q));
        if (q === 0) {
            delete cart[id];
            delete catatan[id]; // menu dihapus dari pesanan: catatannya ikut dibuang
            simpanCatatan();
        } else {
            cart[id] = q;
        }
        App.saveCart(cart);
        render();
    }

    // Kolom catatan milik satu menu saja
    function kolomCatatan(id) {
        const wadah = App.el('div', undefined, 'catatan-item');

        const input = document.createElement('input');
        input.type = 'text';
        input.maxLength = 255;
        input.placeholder = 'Catatan untuk menu ini (opsional)';
        input.value = catatan[id] || '';
        input.setAttribute('aria-label', 'Catatan untuk ' + MENU[id].nama);
        input.addEventListener('input', () => {
            catatan[id] = input.value;
            simpanCatatan();
        });

        const saran = App.el('div', undefined, 'chips-item');
        saranUntuk(id).forEach(teks => {
            saran.append(App.tombol('+ ' + teks, '', () => {
                if (!input.value.toLowerCase().includes(teks.toLowerCase())) {
                    input.value = (input.value ? input.value + ', ' : '') + teks;
                    catatan[id] = input.value;
                    simpanCatatan();
                }
            }));
        });

        wadah.append(input, saran);
        return wadah;
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

            const kartu = App.el('div', undefined, 'card item-checkout');
            const baris = App.el('div', undefined, 'rincian-baris');

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
            kartu.append(baris, kolomCatatan(id));
            daftar.append(kartu);
        });

        document.getElementById('jmlItem').textContent = jml;
        ['subtotal', 'total', 'totalBar'].forEach(i => {
            document.getElementById(i).textContent = App.rp(total);
        });
    }

    document.getElementById('form').addEventListener('submit', e => {
        const ids = Object.keys(cart);
        if (ids.length === 0) {
            e.preventDefault();
            App.toast('Keranjang masih kosong.');
            return;
        }

        const box = document.getElementById('hidden');
        box.replaceChildren();

        function tambah(nama, nilai) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = nama;
            i.value = nilai;
            box.append(i);
        }

        // Catatan dikirim per menu: hanya menu yang memang diberi catatan
        ids.forEach(id => {
            tambah('items[' + id + ']', cart[id]);
            const c = (catatan[id] || '').trim();
            if (c) tambah('catatan[' + id + ']', c);
        });
    });

    render();
})();
