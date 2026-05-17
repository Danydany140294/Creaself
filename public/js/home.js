document.addEventListener('DOMContentLoaded', () => {

    /* ================================================
       TOAST — notification panier (Doc6)
       Injecté dans #toast-container
    ================================================ */
    const toastContainer = document.getElementById('toast-container');

    /**
     * Affiche un toast.
     * @param {string} message  Texte affiché
     * @param {string} icon     Emoji icône (optionnel)
     */
    function showToast(message, icon = '🍪') {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `<span class="toast__icon">${icon}</span><span>${message}</span>`;
        toastContainer.appendChild(toast);

        /* Supprime le toast après 3 s avec animation de sortie */
        setTimeout(() => {
            toast.classList.add('toast--out');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        }, 3000);
    }

    /* ================================================
       AJOUTER AU PANIER — boutons .js-add-to-cart (Doc6)
       Fetch vers /panier/ajouter-produit/:id
       Met à jour le badge panier (#cartBadge)
    ================================================ */
    document.querySelectorAll('.js-add-to-cart').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id   = btn.dataset.id;
            const name = btn.dataset.name;

            /* Feedback visuel immédiat sur le bouton */
            btn.classList.add('is-added');
            btn.textContent = '✓';

            setTimeout(() => {
                btn.classList.remove('is-added');
                btn.textContent = '+';
            }, 900);

            showToast(`${name} ajouté au panier !`, '🛒');

            /* Appel API panier */
            fetch(`/panier/ajouter-produit/${id}`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    'quantite=1',
            })
            .then((res) => {
                if (!res.ok) throw new Error('Erreur réseau');

                /* Mise à jour du badge panier dans la navbar */
                const badge = document.getElementById('cartBadge');
                if (badge) {
                    const current = parseInt(badge.innerText, 10) || 0;
                    badge.innerText = current + 1;
                    badge.classList.add('show');
                }
            })
            .catch((err) => {
                console.error('Erreur ajout panier :', err);
                showToast("Erreur lors de l'ajout", '⚠️');
            });
        });
    });

    /* ================================================
       FAQ — accordion accessible (Doc7)
       Un seul panneau ouvert à la fois
    ================================================ */
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach((item) => {
        const button = item.querySelector('.faq-item__btn');
        const panel  = item.querySelector('.faq-item__panel');

        button.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            /* Ferme tous les items */
            faqItems.forEach((other) => {
                other.classList.remove('is-open');
                other.querySelector('.faq-item__btn').setAttribute('aria-expanded', 'false');
                other.querySelector('.faq-item__panel').hidden = true;
            });

            /* Ouvre l'item cliqué s'il était fermé */
            if (!isOpen) {
                item.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
                panel.hidden = false;
            }
        });
    });

}); /* fin DOMContentLoaded */
