(function () {
    const root = document.getElementById('status-root');
    const kode = root.dataset.kode;
    const api = root.dataset.api;
    const isi = document.getElementById('isi');
    const el = App.el;

    // Pesanan baru berhasil dibuat: kosongkan keranjang
    if (root.dataset.baru === '1') {
        Object.keys(localStorage)
            .filter(k => k.startsWith('cart_meja_'))
            .forEach(k => localStorage.removeItem(k));
    }
    localStorage.setItem('kode_terakhir', kode);

    const LANGKAH = [['Diterima', '✓'], ['Dimasak', '🍳'], ['Disajikan', '🛎️']];
    const POSISI = { diterima: 0, dimasak: 1, siap_disajikan: 2, selesai: 3 };

    // Teks estimasi tetap per status (bukan hitungan waktu sungguhan)
    const INFO = {
        pending:        { persen: 0,   waktu: 'Menunggu pembayaran', catatan: 'Silakan bayar di kasir agar pesanan diproses dapur.' },
        diterima:       { persen: 15,  waktu: '12 - 15 Menit',       catatan: 'Pesanan diterima dapur dan segera dimasak.' },
        dimasak:        { persen: 65,  waktu: '8 - 10 Menit',        catatan: 'Chef Saung sedang memasak pesanan Anda.' },
        siap_disajikan: { persen: 90,  waktu: 'Segera diantar',      catatan: 'Pesanan siap, pelayan sedang mengantar ke meja.' },
        selesai:        { persen: 100, waktu: 'Selesai',             catatan: 'Selamat menikmati hidangan Saung Liwet. Nuhun!' },
    };

    function jam(ts) {
        return ts ? String(ts).replace('T', ' ').substring(11, 16) : '-';
    }

    function tampil(p) {
        isi.replaceChildren();

        const pending = p.status_pembayaran === 'pending';
        const pos = POSISI[p.status_pesanan] ?? 0;
        const info = pending ? Object.assign({}, INFO.pending) : (INFO[p.status_pesanan] || INFO.diterima);
        if (pending && p.metode_pembayaran === 'qris') {
    info.catatan = 'Bayar lewat QRIS di atas, lalu kirim bukti transfer agar kasir bisa memverifikasi.';
}

        // 1. Kepala pesanan + progres langkah
        const kepala = el('div', undefined, 'card kepala');
        kepala.append(
            el('h2', '#' + p.kode_pesanan),
            el('span', 'Saung ' + p.no_meja, 'meja'),
            el('small', 'Dipesan pukul ' + jam(p.tanggal_pesan) + ' WIB • Makan di Tempat')
        );

        const stepper = el('div', undefined, 'stepper');
        LANGKAH.forEach((l, i) => {
            const status = i < pos ? 'done' : (i === pos ? 'now' : '');
            const k = el('div', undefined, 'langkah ' + status);
            k.append(el('div', i < pos ? '✓' : l[1], 'bulat'), el('div', l[0]));
            stepper.append(k);
            if (i < LANGKAH.length - 1) {
                stepper.append(el('div', undefined, 'garis' + (i < pos ? ' done' : '')));
            }
        });
        kepala.append(stepper);
        isi.append(kepala);

        // 2. Estimasi waktu
        const est = el('div', undefined, 'card estimasi');
        const atas = el('div', undefined, 'atas-est');
        atas.append(el('small', '⏱ ESTIMASI WAKTU'), el('span', 'Proses ' + info.persen + '%', 'persen'));
        const luar = el('div', undefined, 'bar-luar');
        const dalam = el('div', undefined, 'bar-isi');
        dalam.style.width = info.persen + '%';
        luar.append(dalam);
        const cat = el('div', undefined, 'catatan-koki');
        cat.append(el('b', 'Catatan Saung: '), document.createTextNode(info.catatan));
        est.append(atas, el('h3', info.waktu), luar, cat);
        isi.append(est);

        // 3. Rincian menu
        let porsi = 0;
        p.detail.forEach(d => { porsi += d.jumlah; });

        const rin = el('div', undefined, 'card');
        const judul = el('div', undefined, 'rincian-judul');
        judul.append(el('b', 'Rincian Menu'), el('span', porsi + ' Porsi'));
        rin.append(judul);

        p.detail.forEach(d => {
            const baris = el('div', undefined, 'menu-baris');
            const kiri = el('div');
            kiri.append(el('div', d.jumlah + '× ' + (d.menu ? d.menu.nama_menu : '-'), 'nama'));
            if (d.catatan) kiri.append(el('div', '🚩 ' + d.catatan, 'cat'));
            baris.append(kiri, el('div', App.rp(d.subtotal), 'sub'));
            rin.append(baris);
        });

        const total = el('div', undefined, 'total-baris');
        total.append(el('span', 'Total Pembayaran'), el('b', App.rp(p.total_bayar)));
        rin.append(total);
        isi.append(rin);
    }

    let timer;

    async function muat() {
        try {
            const res = await fetch(api + '/' + encodeURIComponent(kode), {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error(res.status === 404 ? 'Pesanan tidak ditemukan.' : 'Gagal memuat data.');
            const json = await res.json();
            tampil(json.data);
            if (json.data.status_pesanan === 'selesai') clearInterval(timer);
        } catch (e) {
            isi.replaceChildren(el('div', e.message, 'alert err'));
        }
    }

    document.getElementById('btnNota').addEventListener('click', () => {
        App.toast('Permintaan nota dikirim ke kasir.');
    });

    timer = setInterval(muat, 5000);
    muat();
})();