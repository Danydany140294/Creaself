// ========== NOTIFICATION TOAST GLASS ==========
function showNotification(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const isMobile = window.innerWidth < 768;
    const icon = type === 'success' ? 'add_shopping_cart' : 'error';

    const toast = document.createElement('div');
    toast.className = 'toast-notification glass-card flex items-center shadow-2xl border-l-4 border-l-or';
    toast.style.pointerEvents = 'auto';

    if (isMobile) {
        toast.style.cssText = 'gap: 8px; padding: 8px 10px; border-radius: 12px; max-width: 220px; pointer-events: auto;';
        toast.innerHTML = `
            <div style="background: rgba(204,167,72,0.2); color: #CCA748; padding: 4px; border-radius: 8px; flex-shrink: 0;">
                <span class="material-symbols-outlined" style="font-size: 16px;">${icon}</span>
            </div>
            <div>
                <p class="text-chocolat font-bold" style="font-size: 11px; line-height: 1.2;">${message}</p>
                <p class="text-chocolat/50 uppercase font-bold tracking-widest" style="font-size: 8px;">Délice enregistré</p>
            </div>
        `;
    } else {
        toast.style.cssText = 'gap: 16px; padding: 16px; border-radius: 16px; pointer-events: auto;';
        toast.innerHTML = `
            <div class="bg-or/20 text-or p-2 rounded-xl">
                <span class="material-symbols-outlined">${icon}</span>
            </div>
            <div>
                <p class="text-chocolat font-bold">${message}</p>
                <p class="text-chocolat/50 text-[10px] uppercase font-bold tracking-widest">Délice enregistré</p>
            </div>
        `;
    }

    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========== GESTION DES QUANTITÉS BOXES FIXES ==========
function updateBoxQty(inputId, delta, min, max) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let currentValue = parseInt(input.value) || min;
    let newValue = currentValue + delta;

    if (newValue >= min && newValue <= max) {
        input.value = newValue;
    }
}

// ========== AJOUT AU PANIER BOXES FIXES VIA AJAX ==========
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.box-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const url = form.action;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(() => {
                showNotification('Box ajoutée au panier !', 'success');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter';

                const badge = document.getElementById('cartBadge');
                if (badge) {
                    const current = parseInt(badge.innerText) || 0;
                    badge.innerText = current + 1;
                    badge.classList.add('show');
                }
            })
            .catch(err => {
                console.error('Erreur :', err);
                showNotification("Erreur lors de l'ajout", 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter';
            });
        });
    });
});

// ========== ANIMATION AU SCROLL ==========
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.box-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
});

// ========== GESTION MODAL BOX PERSONNALISABLE ==========
let selectedCookies = {};

function openModalBoxPerso() {
    const modal = document.getElementById('modalBoxPerso');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        updateCounter();
    }
}

function closeModalBoxPerso() {
    const modal = document.getElementById('modalBoxPerso');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetSelection();
    }
}

function resetSelection() {
    selectedCookies = {};
    document.querySelectorAll('.cookie-qty-input').forEach(input => {
        input.value = 0;
        const produitId = input.id.replace('qty_', '');
        updateButtonStates(produitId, 0);
    });
    updateCounter();
}

function updateCookieQty(produitId, delta, maxStock) {
    const input = document.getElementById(`qty_${produitId}`);
    if (!input) return;

    let currentQty = parseInt(input.value) || 0;
    let newQty = currentQty + delta;
    let totalCookies = getTotalCookies() - currentQty;

    if (newQty < 0) newQty = 0;
    if (newQty > maxStock) newQty = maxStock;
    if (totalCookies + newQty > 12) newQty = 12 - totalCookies;

    input.value = newQty;

    if (newQty > 0) {
        selectedCookies[`produit_${produitId}`] = newQty;
    } else {
        delete selectedCookies[`produit_${produitId}`];
    }

    updateButtonStates(produitId, newQty);
    updateCounter();
}

function updateButtonStates(produitId, qty) {
    const card = document.querySelector(`[data-produit-id="${produitId}"]`);
    if (!card) return;

    const minusBtn = card.querySelector('.btn-minus');
    const plusBtn = card.querySelector('.btn-plus');

    if (minusBtn) minusBtn.disabled = qty === 0;
    if (plusBtn) plusBtn.disabled = getTotalCookies() >= 12;
}

function getTotalCookies() {
    return Object.values(selectedCookies).reduce((sum, qty) => sum + qty, 0);
}

function updateCounter() {
    const total = getTotalCookies();
    const counterElement = document.getElementById('cookiesSelected');
    const progressBar = document.getElementById('progressBar');
    const btnValider = document.getElementById('btnValiderBox');

    if (!counterElement || !progressBar || !btnValider) return;

    counterElement.textContent = total;
    progressBar.style.width = `${(total / 12) * 100}%`;

    if (total === 12) {
        progressBar.style.backgroundColor = '#10b981';
        btnValider.disabled = false;
    } else if (total > 12) {
        progressBar.style.backgroundColor = '#ef4444';
        btnValider.disabled = true;
    } else {
        progressBar.style.backgroundColor = '#3b82f6';
        btnValider.disabled = true;
    }

    document.querySelectorAll('.cookie-card').forEach(card => {
        const produitId = card.getAttribute('data-produit-id');
        const input = document.getElementById(`qty_${produitId}`);
        if (input) updateButtonStates(produitId, parseInt(input.value) || 0);
    });
}

async function validerBoxPerso() {
    const total = getTotalCookies();

    if (total !== 12) {
        showNotification('Sélectionnez exactement 12 cookies !', 'error');
        return;
    }

    try {
        const response = await fetch('/panier/ajouter-box-personnalisable', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cookies: selectedCookies })
        });

        const result = await response.json();

        if (result.success) {
            closeModalBoxPerso();
            showNotification(result.message || 'Box ajoutée au panier !', 'success');
            setTimeout(() => window.location.href = window.location.pathname, 1500);
        } else {
            showNotification(result.message || 'Une erreur est survenue', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification("Erreur lors de l'ajout au panier", 'error');
    }
}

// ========== FERMETURE MODAL ==========
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalBoxPerso');
    if (modal && event.target === modal) closeModalBoxPerso();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeModalBoxPerso();
});

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#modalBoxPerso') openModalBoxPerso();
});
