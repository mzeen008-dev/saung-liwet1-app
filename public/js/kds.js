(function () {
    const root = document.getElementById('kds');
    const URL_DATA = root.dataset.url;
    const URL_UPDATE = root.dataset.update;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const BATAS_URGENT = 15; // menit

    const KOLOM = {
        diterima:       { id: 'kol-diterima', hitung: 'n-diterima', tombol: 'Mulai Masak',               kelas: 'btn-mulai' },
        dimasak:        { id: 'kol-dimasak',  hitung: 'n-dimasak',  tombol: 'Siap Saji',                 kelas: 'btn-siap' },
        siap_disajikan: { id: 'kol-siap',     hitung: 'n-siap',     tombol: 'Selesai / Diambil Waiter',  kelas: 'btn-selesai' },
    };

    const info = document.getElementById('kds-info');
    const btnBuzzer = document.getElementById('buzzer');
    let buzzer = true;
    let dikenal = null;   // id tiket yang sudah pernah tampil
    let audio = null;

    function el(tag, teks, kelas) {
        const e = document.createElement(tag);
        if (teks !== undefined) e.textContent = teks;
        if (kelas) e.className = kelas;
        return e;
    }

    function tampilInfo(teks) {
        info.textContent = teks;
        info.style.display = teks ? 'block' : 'none';
    }

    function bip() {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            const osc = audio.createOscillator();
            const gain = audio.createGain();
            osc.frequency.value = 880;
            gain.gain.value = 0.15;
            osc.connect(gain);
            gain.connect(audio.destination);
            osc.start();
            osc.stop(audio.currentTime + 0.25);
        } catch (e) { /* browser memblokir suara: abaikan */ }
    }

    function kartu(t) {
        const urgent = t.status !== 'siap_disajikan' && t.menit >= BATAS_URGENT;
        const k = KOLOM[t.status];

        const box = el('article', undefined, 'tiket' + (urgent ? ' urgent' : ''));

        const kepala = el('div', undefined, 'kepala');
        const kiri = el('div', undefined, 'kiri');
        kiri.append(el('b', 'Saung ' + t.meja), el('small', '#' + t.kode));
        const kanan = el('div', undefined, 'kanan');
        kanan.append(el('b', t.menit + ' mnt'), el('span', (urgent ? 'URGENT • ' : '') + 'Masuk ' + t.jam));
        kepala.append(kiri, kanan);

        const isi = el('div', undefined, 'isi');
        isi.append(el('div', 'Pemesan: ' + t.nama, 'pemesan'));
        t.item.forEach(i => {
            const baris = el('div', undefined, 'item-baris');
            baris.append(el('b', i.jumlah + '× '), document.createTextNode(i.nama));
            if (i.catatan) baris.append(el('span', 'Catatan: ' + i.catatan, 'cat'));
            isi.append(baris);
        });

        const aksi = el('div', undefined, 'aksi');
        const tombol = el('button', k.tombol, k.kelas);
        tombol.type = 'button';
        tombol.addEventListener('click', () => ubahStatus(t.id, tombol));
        aksi.append(tombol);

        box.append(kepala, isi, aksi);
        return box;
    }

    function render(tiket) {
        Object.values(KOLOM).forEach(k => {
            document.getElementById(k.id).replaceChildren();
            document.getElementById(k.hitung).textContent = '0';
        });

        const hitung = { diterima: 0, dimasak: 0, siap_disajikan: 0 };
        tiket.forEach(t => {
            if (!KOLOM[t.status]) return;
            document.getElementById(KOLOM[t.status].id).append(kartu(t));
            hitung[t.status]++;
        });

        Object.keys(KOLOM).forEach(s => {
            const kol = document.getElementById(KOLOM[s].id);
            document.getElementById(KOLOM[s].hitung).textContent = hitung[s];
            if (hitung[s] === 0) kol.append(el('div', 'Tidak ada tiket.', 'kosong-kol'));
        });

        document.getElementById('stat-aktif').textContent = tiket.length + ' Tiket Aktif';
        const proses = tiket.filter(t => t.status !== 'siap_disajikan');
        const rata = proses.length ? Math.round(proses.reduce((a, t) => a + t.menit, 0) / proses.length) : 0;
        document.getElementById('stat-rata').textContent = rata + ' Menit';

        // Bunyi buzzer hanya untuk tiket baru (bukan saat pertama kali halaman dibuka)
        const idSekarang = new Set(tiket.map(t => t.id));
        if (dikenal !== null && buzzer) {
            const adaBaru = tiket.some(t => t.status === 'diterima' && !dikenal.has(t.id));
            if (adaBaru) bip();
        }
        dikenal = idSekarang;
    }

    async function muat() {
        try {
            const res = await fetch(URL_DATA, { headers: { 'Accept': 'application/json' } });
            if (res.status === 401 || res.status === 419) {
                tampilInfo('Sesi habis. Silakan login ulang.');
                return;
            }
            if (!res.ok) throw new Error('Gagal memuat tiket.');
            const json = await res.json();
            tampilInfo('');
            render(json.tiket);
        } catch (e) {
            tampilInfo('Koneksi terputus. Mencoba lagi...');
        }
    }

    async function ubahStatus(id, tombol) {
        tombol.disabled = true;
        try {
            const res = await fetch(URL_UPDATE.replace('__ID__', id), {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) tampilInfo(json.message || 'Gagal mengubah status.');
        } catch (e) {
            tampilInfo('Koneksi terputus. Coba lagi.');
        }
        muat();
    }

    btnBuzzer.addEventListener('click', () => {
        buzzer = !buzzer;
        btnBuzzer.textContent = buzzer ? '🔔 Buzzer ON' : '🔕 Buzzer OFF';
        btnBuzzer.classList.toggle('on', buzzer);
        if (buzzer) bip(); // pratinjau suara sekaligus mengaktifkan audio browser
    });

    function jam() {
        document.getElementById('jam').textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }

    jam();
    setInterval(jam, 1000);
    muat();
    setInterval(muat, 5000);
})();
