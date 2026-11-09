(function () {
    const input = document.getElementById('foto');
    const pratinjau = document.getElementById('pratinjau');
    const galat = document.getElementById('foto-galat');
    const MAKS = 4 * 1024 * 1024; // sama dengan batas validasi di server (4 MB)

    if (!input) return;

    input.addEventListener('change', () => {
        galat.style.display = 'none';
        pratinjau.hidden = true;

        const file = input.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            galat.textContent = 'Foto harus berupa JPG, PNG, atau WEBP.';
            galat.style.display = 'block';
            input.value = '';
            return;
        }
        if (file.size > MAKS) {
            galat.textContent = 'Ukuran foto maksimal 4 MB. Kecilkan dulu fotonya.';
            galat.style.display = 'block';
            input.value = '';
            return;
        }

        // Pratinjau memakai data URL (blob: diblokir oleh Content-Security-Policy)
        const baca = new FileReader();
        baca.onload = () => {
            pratinjau.src = baca.result;
            pratinjau.hidden = false;
        };
        baca.readAsDataURL(file);
    });
})();
