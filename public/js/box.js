// ========================================
// CONFIG BOX PERSONNALISÉE (DYNAMIQUE)
// ========================================

// Valeur par défaut (fallback)
let MAX_COOKIES = 12;

let selectedCookies = {};

// ========================================
// INITIALISATION (option future Twig)
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    const el = document.body;

    // Si tu veux brancher Twig plus tard :
    // <body data-max-cookies="6|12|24">
    if (el.dataset.maxCookies) {
        const value = parseInt(el.dataset.maxCookies);
        if (!isNaN(value)) {
            MAX_COOKIES = value;
        }
    }

    updateCounter();
});

// ========================================
// SET BOX SIZE (6 / 12 / 24)
// ========================================
function setBoxSize(size) {
    const newSize = parseInt(size);

    if (isNaN(newSize)) return;

    MAX_COOKIES = newSize;

    // Mettre à jour la classe active sur les boutons
    document.querySelectorAll('.switch-btn').forEach(btn => {
        btn.classList.remove('active');
        if (parseInt(btn.textContent.trim()) === newSize) {
            btn.classList.add('active');
        }
    });

    resetSelection();
    updateCounter();
}

// ========================================
// TOAST NOTIFICATION
// ========================================
function showNotification(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const icon = type === 'success' ? 'check_circle' : 'error';

    const toast = document.createElement('div');
    toast.className = 'toast-notification glass-card';

    toast.innerHTML = `
        <div style="display:flex; gap:10px; align-items:center;">
            <span class="material-symbols-outlined">${icon}</span>
            <div>
                <strong>${message}</strong>
            </div>
        </div>
    `;

    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========================================
// BOX FIXE (NE PAS TOUCHER LOGIQUE)
// ========================================
function updateBoxQty(inputId, delta, min, max) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let value = parseInt(input.value) || min;
    value = Math.max(min, Math.min(max, value + delta));

    input.value = value;
}

// ========================================
// MODAL OPEN / CLOSE
// ========================================
function openModalBoxPerso() {
    const modal = document.getElementById('modalBoxPerso');
    if (!modal) return;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    updateCounter();
}

function closeModalBoxPerso() {
    const modal = document.getElementById('modalBoxPerso');
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = 'auto';

    resetSelection();
}

// ========================================
// RESET SELECTION
// ========================================
function resetSelection() {
    selectedCookies = {};

    document.querySelectorAll('.cookie-qty-input').forEach(input => {
        input.value = 0;
    });

    document.querySelectorAll('.cookie-btn').forEach(btn => {
        btn.disabled = false;
    });

    updateCounter();
}

// ========================================
// UPDATE COOKIE QTY
// ========================================
function updateCookieQty(produitId, delta, maxStock) {
    const input = document.getElementById(`qty_${produitId}`);
    if (!input) return;

    let current = parseInt(input.value) || 0;
    let newQty = current + delta;

    const total = getTotalCookies();
    const otherCookies = total - current;

    // limites stock + box size
    newQty = Math.max(0, Math.min(maxStock, newQty));

    if (otherCookies + newQty > MAX_COOKIES) {
        newQty = MAX_COOKIES - otherCookies;
    }

    input.value = newQty;

    if (newQty > 0) {
        selectedCookies[produitId] = newQty;
    } else {
        delete selectedCookies[produitId];
    }

    updateCounter();
}

// ========================================
// TOTAL
// ========================================
function getTotalCookies() {
    return Object.values(selectedCookies).reduce((a, b) => a + b, 0);
}

// ========================================
// UI UPDATE
// ========================================
function updateCounter() {
    const total = getTotalCookies();

    const counter = document.getElementById('cookiesSelected');
    const progress = document.getElementById('progressBar');
    const btn = document.getElementById('btnValiderBox');

    if (counter) counter.textContent = total;

    const maxUI = document.getElementById('maxCookiesUI');
if (maxUI) maxUI.textContent = MAX_COOKIES;

    if (progress) {
        progress.style.width = `${(total / MAX_COOKIES) * 100}%`;
    }

    if (btn) {
        btn.disabled = total !== MAX_COOKIES;
    }

    // update buttons state
    document.querySelectorAll('.cookie-card').forEach(card => {
        const id = card.dataset.produitId;
        const input = document.getElementById(`qty_${id}`);
        if (!input) return;

        const qty = parseInt(input.value) || 0;

        const minus = card.querySelector('.btn-minus');
        const plus = card.querySelector('.btn-plus');

        if (minus) minus.disabled = qty <= 0;
        if (plus) plus.disabled = total >= MAX_COOKIES;
    });
}

// ========================================
// VALIDATION
// ========================================
async function validerBoxPerso() {
    const total = getTotalCookies();

    if (total !== MAX_COOKIES) {
        showNotification(`Sélectionne exactement ${MAX_COOKIES} cookies`, 'error');
        return;
    }

    try {
        const response = await fetch('/panier/ajouter-box-personnalisable', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
    cookies: selectedCookies,
    taille: MAX_COOKIES
})
        });

        const result = await response.json();

        if (result.success) {
            closeModalBoxPerso();
            showNotification('Box ajoutée au panier !', 'success');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }

    } catch (e) {
        showNotification("Erreur serveur", 'error');
    }
}

// ========================================
// EVENTS MODAL CLOSE
// ========================================
window.addEventListener('click', e => {
    const modal = document.getElementById('modalBoxPerso');
    if (modal && e.target === modal) closeModalBoxPerso();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModalBoxPerso();
});