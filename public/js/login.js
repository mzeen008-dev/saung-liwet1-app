(function () {
    const tombol = document.getElementById('tampil');
    const isian = document.getElementById('password');

    tombol.addEventListener('click', () => {
        const sembunyi = isian.type === 'password';
        isian.type = sembunyi ? 'text' : 'password';
        tombol.textContent = sembunyi ? '🙈 Sembunyikan' : '👁 Tampilkan';
    });
})();
