
document.addEventListener('DOMContentLoaded', () => {

    /* ================================================
       TOAST — notification panier
    ================================================ */
    const toastContainer = document.getElementById('toast-container');

    /**
     * Affiche un toast.
     * @param {string} message
     * @param {string} icon
     */
    function showToast(message, icon = '🍪') {

        if (!toastContainer) {
            return;
        }

        const toast = document.createElement('div');

        toast.className = 'toast';

        toast.innerHTML = `
            <span class="toast__icon">${icon}</span>
            <span>${message}</span>
        `;

        toastContainer.appendChild(toast);

        /* Supprime le toast après 3s */
        setTimeout(() => {

            toast.classList.add('toast--out');

            toast.addEventListener(
                'animationend',
                () => toast.remove(),
                { once: true }
            );

        }, 3000);
    }

    /* ================================================
       AJOUT AU PANIER
    ================================================ */
    const addToCartButtons = document.querySelectorAll('.js-add-to-cart');

    addToCartButtons.forEach((button) => {

        button.addEventListener('click', async () => {

            const id = button.dataset.id;
            const name = button.dataset.name;

            /* Feedback visuel */
            button.classList.add('is-added');
            button.textContent = '✓';

            setTimeout(() => {
                button.classList.remove('is-added');
                button.textContent = '+';
            }, 900);

            showToast(`${name} ajouté au panier !`, '🛒');

            try {

                const response = await fetch(
                    `/panier/ajouter-produit/${id}`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'quantite=1',
                    }
                );

                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                /* Mise à jour badge panier */
                const badge = document.getElementById('cartBadge');

                if (badge) {

                    const current =
                        parseInt(badge.innerText, 10) || 0;

                    badge.innerText = current + 1;

                    badge.classList.add('show');
                }

            } catch (error) {

                console.error(
                    'Erreur ajout panier :',
                    error
                );

                showToast(
                    "Erreur lors de l'ajout",
                    '⚠️'
                );
            }

        });

    });

    /* ================================================
       FAQ — accordion accessible
    ================================================ */
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach((item) => {

        const button = item.querySelector('.faq-item__btn');
        const panel = item.querySelector('.faq-item__panel');

        if (!button || !panel) {
            return;
        }

        button.addEventListener('click', () => {

            const isOpen =
                item.classList.contains('is-open');

            /* Ferme tous les items */
            faqItems.forEach((otherItem) => {

                otherItem.classList.remove('is-open');

                const otherButton =
                    otherItem.querySelector('.faq-item__btn');

                const otherPanel =
                    otherItem.querySelector('.faq-item__panel');

                if (otherButton) {
                    otherButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

                if (otherPanel) {
                    otherPanel.hidden = true;
                }

            });

            /* Ouvre l'item cliqué */
            if (!isOpen) {

                item.classList.add('is-open');

                button.setAttribute(
                    'aria-expanded',
                    'true'
                );

                panel.hidden = false;
            }

        });

    });

    /* ================================================
       MODAL SUCCÈS HOMEPAGE
       Version isolée
    ================================================ */
    const successModal =
        document.getElementById('success-modal');

    if (successModal) {

        window.closeSuccessModal = async function () {

            successModal.classList.add(
                'home-modal-overlay--hiding'
            );

            setTimeout(async () => {

                try {

                    await fetch(
                        '/clear-commande-session',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type':
                                    'application/json',
                            },
                        }
                    );

                } catch (error) {

                    console.error(
                        'Erreur fermeture modal :',
                        error
                    );
                }

                successModal.remove();

            }, 300);
        };
    }

});