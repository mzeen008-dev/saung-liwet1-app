(function () {
    const root = document.getElementById('pos');
    const URL_DATA = root.dataset.url;
    const URL_VERIF = root.dataset.verifikasi;
    const URL_STRUK = root.dataset.struk;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const info = document.getElementById('pos-info');
    const daftar = document.getElementById('daftar');
    const panel = document.getElementById('panel');

    let tab = 'semua';
    let dipilih = null;
    let data = { menunggu: [], lunas: [], ringkas: { kas_hari_ini: 0, meja_terisi: 0, meja_total: 0 } };
    let sigDetail = '';

    function el(tag, teks, kelas) {
        const e = document.createElement(tag);
        if (teks !== undefined) e.textContent = teks;
        if (kelas) e.className = kelas;
        return e;
    }

    function rp(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    // Ambil angka murni dari teks seperti "100.000"
    function angka(teks) {
        return parseInt(String(teks).replace(/[^\d]/g, ''), 10) || 0;
    }

    // Pecahan uang yang mungkin dibayarkan pelanggan (uang pas dan pembulatan ke atas)
    function saran(total) {
        const s = new Set([Math.round(total)]);
        [5000, 10000, 20000, 50000, 100000].forEach(k => s.add(Math.ceil(total / k) * k));
        return Array.from(s).filter(v => v >= total).sort((a, b) => a - b).slice(0, 4);
    }

    function tampilInfo(teks) {
        info.textContent = teks;
        info.style.display = teks ? 'block' : 'none';
    }

    function semua() {
        return data.menunggu.concat(data.lunas);
    }

    function daftarAktif() {
        if (tab === 'menunggu') return data.menunggu;
        if (tab === 'lunas') return data.lunas;
        return semua();
    }

    function label(p) {
        if (p.bayar === 'pending') {
            return { t: p.metode === 'qris' ? 'Menunggu Verifikasi QRIS' : 'Menunggu Bayar Tunai', k: 'tunggu' };
        }
        return { t: 'Lunas • ' + p.status.split('_').join(' '), k: 'lunas' };
    }

    function ringkasItem(p) {
        return p.item.map(i => i.jumlah + '× ' + i.nama).join(', ');
    }

    function kartu(p) {
        const l = label(p);
        const k = el('article', undefined, 'psn' + (p.id === dipilih ? ' on' : ''));
        k.addEventListener('click', () => {
            dipilih = p.id;
            sigDetail = '';
            renderDaftar();
            renderDetail();
        });

        const atas = el('div', undefined, 'psn-atas');
        const kiri = el('div', undefined, 'psn-kiri');
        const nama = el('div');
        nama.append(el('b', p.nama), el('small', p.jam + ' • ' + p.kode));
        kiri.append(el('span', p.meja, 'no-meja'), nama);
        atas.append(kiri, el('span', l.t, 'label-status ' + l.k));

        const total = el('div', undefined, 'psn-total');
        total.append(el('span', p.metode === 'qris' ? 'QRIS' : 'Tunai'), el('b', rp(p.total)));

        k.append(atas, el('div', ringkasItem(p), 'psn-item'), total);
        return k;
    }

    function renderDaftar() {
        daftar.replaceChildren();
        const list = daftarAktif();
        if (list.length === 0) {
            daftar.append(el('div', 'Tidak ada pesanan di tab ini.', 'kosong-pos'));
        }
        list.forEach(p => daftar.append(kartu(p)));
    }

    function renderStat() {
        const r = data.ringkas;
        document.getElementById('st-kas').textContent = rp(r.kas_hari_ini);
        const persen = r.meja_total ? Math.round(r.meja_terisi / r.meja_total * 100) : 0;
        document.getElementById('st-okupansi').textContent = r.meja_terisi + '/' + r.meja_total + ' Terisi (' + persen + '%)';
        document.getElementById('n-semua').textContent = data.menunggu.length + data.lunas.length;
        document.getElementById('n-menunggu').textContent = data.menunggu.length;
        document.getElementById('n-lunas').textContent = data.lunas.length;
    }

    // Kotak input uang tunai + kembalian. onUbah dipanggil setiap nilai berubah.
    function kotakTunai(p, onUbah) {
        const box = el('div', undefined, 'kotak-tunai');
        box.append(el('label', 'Uang diterima dari pelanggan'));

        const baris = el('div', undefined, 'input-uang');
        const input = document.createElement('input');
        input.type = 'text';
        input.inputMode = 'numeric';
        input.autocomplete = 'off';
        input.placeholder = '0';
        input.setAttribute('aria-label', 'Uang diterima');
        baris.append(el('span', 'Rp'), input);
        box.append(baris);

        const cepat = el('div', undefined, 'uang-cepat');
        saran(p.total).forEach((v, i) => {
            const chip = el('button', i === 0 ? 'Uang Pas' : rp(v), 'uang-chip');
            chip.type = 'button';
            chip.addEventListener('click', () => {
                input.value = v.toLocaleString('id-ID');
                onUbah();
            });
            cepat.append(chip);
        });
        box.append(cepat);

        const hasil = el('div', undefined, 'hasil-kembali netral');
        box.append(hasil);

        function perbarui() {
            const uang = angka(input.value);
            const selisih = uang - p.total;
            hasil.replaceChildren();
            if (uang === 0) {
                hasil.className = 'hasil-kembali netral';
                hasil.append(el('span', 'Kembalian'), el('b', 'Rp 0'));
            } else if (selisih >= 0) {
                hasil.className = 'hasil-kembali cukup';
                hasil.append(el('span', 'Kembalian'), el('b', rp(selisih)));
            } else {
                hasil.className = 'hasil-kembali kurang';
                hasil.append(el('span', 'Uang kurang'), el('b', rp(-selisih)));
            }
            return uang;
        }

        input.addEventListener('input', () => {
            const n = angka(input.value);
            input.value = n > 0 ? n.toLocaleString('id-ID') : '';
            onUbah();
        });

        return { box, input, perbarui };
    }

    function renderDetail() {
        const p = semua().find(x => x.id === dipilih);
        const sig = JSON.stringify(p || null);
        if (sig === sigDetail) return; // tidak ada perubahan: jangan gambar ulang (input dan gambar tidak berkedip)
        sigDetail = sig;

        panel.replaceChildren();

        if (!p) {
            panel.append(el('div', 'Pilih pesanan di sebelah kiri untuk memeriksa pembayaran.', 'kosong-panel'));
            return;
        }

        const l = label(p);
        panel.append(
            el('h4', 'Saung ' + p.meja + ' • ' + p.nama),
            el('div', '#' + p.kode + ' • masuk ' + p.jam + (p.hp ? ' • ' + p.hp : ''), 'sub')
        );
        const st = el('div', undefined, 'bar-status');
        st.append(el('span', l.t, 'label-status ' + l.k));
        panel.append(st);

        p.item.forEach(i => {
            const baris = el('div', undefined, 'baris');
            const kiri = el('div');
            kiri.append(document.createTextNode(i.jumlah + '× ' + i.nama));
            if (i.catatan) kiri.append(el('small', 'Catatan: ' + i.catatan));
            baris.append(kiri, el('b', rp(i.subtotal)));
            panel.append(baris);
        });

        if (p.bayar === 'pending' && p.metode === 'qris') {
            const box = el('div', undefined, 'bukti-box');
            box.append(el('b', 'Bukti Transfer QRIS'));
            if (p.ada_bukti) {
                const a = document.createElement('a');
                a.href = p.bukti_url;
                a.target = '_blank';
                a.rel = 'noopener';
                const img = document.createElement('img');
                img.src = p.bukti_url;
                img.alt = 'Bukti transfer ' + p.kode;
                img.className = 'bukti-img';
                a.append(img);
                box.append(a, el('small', 'Cocokkan nominal ' + rp(p.total) + ', nama pengirim, dan waktu transfer sebelum menekan Verifikasi.'));
            } else {
                box.append(el('div', 'Pelanggan belum mengunggah bukti transfer.', 'bukti-kosong'));
            }
            panel.append(box);
        }

        const ringkas = el('div', undefined, 'ringkas');
        const r1 = el('div'); r1.append(el('span', 'Subtotal Pesanan'), el('b', rp(p.total)));
        const r2 = el('div'); r2.append(el('span', 'Biaya Layanan Saung'), el('b', 'Gratis'));
        ringkas.append(r1, r2);
        panel.append(ringkas);

        const tot = el('div', undefined, 'total-besar');
        tot.append(el('span', 'TOTAL TAGIHAN'), el('b', rp(p.total)));
        panel.append(tot);

        // Pesanan tunai yang sudah lunas: tampilkan uang diterima dan kembalian (ikut tercetak di struk)
        if (p.bayar === 'lunas' && p.metode === 'tunai' && p.uang !== null) {
            const u = el('div', undefined, 'baris-uang');
            u.append(el('span', 'Uang diterima'), el('b', rp(p.uang)));
            const k = el('div', undefined, 'baris-uang kembali');
            k.append(el('span', 'Kembalian'), el('b', rp(p.kembali)));
            panel.append(u, k);
        }

        let tunai = null;
        let btnVerif = null;

        function sinkron() {
            if (!tunai) return;
            const uang = tunai.perbarui();
            if (btnVerif) btnVerif.disabled = uang < p.total;
        }

        if (p.bayar === 'pending' && p.metode === 'tunai') {
            tunai = kotakTunai(p, sinkron);
            panel.append(tunai.box);
        }

        const aksi = el('div', undefined, 'aksi-panel');
        if (p.bayar === 'pending') {
            btnVerif = el('button', 'Verifikasi Lunas', 'btn-verif');
            btnVerif.type = 'button';
            btnVerif.addEventListener('click', () => verifikasi(p, btnVerif, tunai ? tunai.input : null));
            aksi.append(btnVerif);
        } else {
            const cetak = el('button', '🖨 Cetak Struk', 'btn-garis');
            cetak.type = 'button';
            cetak.addEventListener('click', () => bukaStruk(p));
            aksi.append(cetak);
        }
        panel.append(aksi);

        sinkron(); // tombol tunai mulai dalam keadaan nonaktif sampai uang cukup
    }

    // Buka halaman struk (58/80 mm) di jendela kecil dan langsung tampilkan dialog cetak
    function bukaStruk(p) {
        const lebar = localStorage.getItem('lebar_struk') === '58' ? '58' : '80';
        const url = URL_STRUK.replace('__ID__', p.id) + '?lebar=' + lebar + '&cetak=1';
        window.open(url, '_blank', 'width=420,height=720');
    }

    async function verifikasi(p, btn, input) {
        const opsi = { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } };

        if (p.metode === 'tunai') {
            const uang = input ? angka(input.value) : 0;
            if (uang < p.total) {
                tampilInfo('Uang yang diterima kurang dari total tagihan.');
                return;
            }
            opsi.headers['Content-Type'] = 'application/json';
            opsi.body = JSON.stringify({ uang_diterima: uang });
        } else if (!p.ada_bukti) {
            if (!confirm('Bukti transfer belum ada. Tetap verifikasi sebagai lunas?')) return;
        }

        btn.disabled = true;
        try {
            const res = await fetch(URL_VERIF.replace('__ID__', p.id), opsi);
            const json = await res.json().catch(() => ({}));
            const galat = json.errors ? Object.values(json.errors)[0][0] : null;
            tampilInfo(galat || json.message || (res.ok ? 'Pembayaran diverifikasi.' : 'Gagal memverifikasi.'));
        } catch (e) {
            tampilInfo('Koneksi terputus. Coba lagi.');
        }
        sigDetail = '';
        muat();
    }

    async function muat() {
        try {
            const res = await fetch(URL_DATA, { headers: { 'Accept': 'application/json' } });
            if (res.status === 401 || res.status === 419) {
                tampilInfo('Sesi habis. Silakan login ulang.');
                return;
            }
            if (!res.ok) throw new Error('gagal');
            data = await res.json();

            // Jaga pilihan: kalau pesanan yang dipilih hilang, pilih yang pertama
            const ada = semua().some(x => x.id === dipilih);
            if (!ada) {
                const awal = daftarAktif()[0] || semua()[0];
                dipilih = awal ? awal.id : null;
                sigDetail = '';
            }

            renderStat();
            renderDaftar();
            renderDetail();
        } catch (e) {
            tampilInfo('Koneksi terputus. Mencoba lagi...');
        }
    }

    document.querySelectorAll('.tab-pos button').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.tab-pos button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            tab = b.dataset.tab;
            renderDaftar();
        });
    });

    muat();
    setInterval(muat, 5000);
})();
