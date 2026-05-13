/* perline-footer.js */
document.addEventListener('DOMContentLoaded', function () {

    const toggles = document.querySelectorAll('.footer-toggle');

    toggles.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Sur desktop (> 640px) : pas d'action
            if (window.innerWidth > 640) return;

            const isOpen = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });
    });

    // Réinitialiser au redimensionnement (mobile → desktop)
    window.addEventListener('resize', function () {
        if (window.innerWidth > 640) {
            toggles.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
        }
    });
});