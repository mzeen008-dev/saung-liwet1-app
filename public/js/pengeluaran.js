document.querySelectorAll('form[data-konfirmasi]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
        if (!window.confirm(f.dataset.konfirmasi)) {
            e.preventDefault();
        }
    });
});