// ========== GESTION DES QUANTITÉS BOXES FIXES ==========
function updateBoxQty(inputId, delta, min, max) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let currentValue = parseInt(input.value) || min;
    let newValue = currentValue + delta;
    
    // Limiter entre min et max
    if (newValue >= min && newValue <= max) {
        input.value = newValue;
    }
}

// ========== ANIMATION AU SCROLL ==========
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.box-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Animation décalée pour chaque carte
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

    // Préparer les cartes pour l'animation
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
});

// ========== GESTION MODAL BOX PERSONNALISABLE ==========
let selectedCookies = {};

// Ouvrir la modal
function openModalBoxPerso() {
    console.log('🚀 Ouverture de la modal');
    const modal = document.getElementById('modalBoxPerso');
    
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        console.log('✅ Modal affichée avec succès');
        
        // Reset au cas où
        updateCounter();
    } else {
        console.error('❌ Modal #modalBoxPerso introuvable dans le DOM !');
    }
}

// Fermer la modal
function closeModalBoxPerso() {
    const modal = document.getElementById('modalBoxPerso');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetSelection();
    }
}

// Réinitialiser la sélection
function resetSelection() {
    selectedCookies = {};
    
    // Reset tous les inputs
    document.querySelectorAll('.cookie-qty-input').forEach(input => {
        input.value = 0;
        const produitId = input.id.replace('qty_', '');
        updateButtonStates(produitId, 0);
    });
    
    updateCounter();
}

// Mettre à jour la quantité d'un cookie
function updateCookieQty(produitId, delta, maxStock) {
    const input = document.getElementById(`qty_${produitId}`);
    if (!input) {
        console.error(`Input qty_${produitId} non trouvé`);
        return;
    }
    
    let currentQty = parseInt(input.value) || 0;
    let newQty = currentQty + delta;
    
    // Calculer le total actuel sans ce cookie
    let totalCookies = getTotalCookies() - currentQty;
    
    // Contraintes
    if (newQty < 0) newQty = 0;
    if (newQty > maxStock) newQty = maxStock;
    if (totalCookies + newQty > 12) newQty = 12 - totalCookies;
    
    // Mettre à jour l'input
    input.value = newQty;
    
    // Mettre à jour l'objet selectedCookies
    if (newQty > 0) {
        selectedCookies[`produit_${produitId}`] = newQty;
    } else {
        delete selectedCookies[`produit_${produitId}`];
    }
    
    // Mettre à jour les états des boutons
    updateButtonStates(produitId, newQty);
    
    // Mettre à jour le compteur global
    updateCounter();
}

// Mettre à jour les états des boutons +/-
function updateButtonStates(produitId, qty) {
    const card = document.querySelector(`[data-produit-id="${produitId}"]`);
    if (!card) return;
    
    const minusBtn = card.querySelector('.btn-minus');
    const plusBtn = card.querySelector('.btn-plus');
    
    // Bouton moins : désactivé si qty = 0
    if (minusBtn) {
        minusBtn.disabled = qty === 0;
    }
    
    // Bouton plus : désactivé si total >= 12
    if (plusBtn) {
        const totalCookies = getTotalCookies();
        plusBtn.disabled = totalCookies >= 12;
    }
}

// Calculer le total de cookies sélectionnés
function getTotalCookies() {
    return Object.values(selectedCookies).reduce((sum, qty) => sum + qty, 0);
}

// Mettre à jour le compteur et la barre de progression
function updateCounter() {
    const total = getTotalCookies();
    const counterElement = document.getElementById('cookiesSelected');
    const progressBar = document.getElementById('progressBar');
    const btnValider = document.getElementById('btnValiderBox');
    
    if (!counterElement || !progressBar || !btnValider) {
        console.error('Éléments manquants pour le compteur');
        return;
    }
    
    // Mettre à jour le compteur
    counterElement.textContent = total;
    
    // Mettre à jour la barre de progression
    const percentage = (total / 12) * 100;
    progressBar.style.width = `${percentage}%`;
    
    // Changer la couleur selon l'état
    if (total === 12) {
        progressBar.style.backgroundColor = '#10b981'; // Vert
        btnValider.disabled = false;
    } else if (total > 12) {
        progressBar.style.backgroundColor = '#ef4444'; // Rouge
        btnValider.disabled = true;
    } else {
        progressBar.style.backgroundColor = '#3b82f6'; // Bleu
        btnValider.disabled = true;
    }
    
    // Mettre à jour tous les boutons plus
    document.querySelectorAll('.cookie-card').forEach(card => {
        const produitId = card.getAttribute('data-produit-id');
        const input = document.getElementById(`qty_${produitId}`);
        if (input) {
            const currentQty = parseInt(input.value) || 0;
            updateButtonStates(produitId, currentQty);
        }
    });
}

// Valider et ajouter la box personnalisée au panier
async function validerBoxPerso() {
    const total = getTotalCookies();
    
    // Vérifier qu'on a exactement 12 cookies
    if (total !== 12) {
        alert('Vous devez sélectionner exactement 12 cookies !');
        return;
    }
    
    // Préparer les données
    const data = {
        cookies: selectedCookies
    };
    
    console.log('📦 Envoi des données:', data);
    
    try {
        const response = await fetch('/panier/ajouter-box-personnalisable', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModalBoxPerso();
            alert(result.message || 'Box personnalisée ajoutée au panier !');
            
            // Recharger la page pour mettre à jour le panier
            location.reload();
        } else {
            alert(result.message || 'Une erreur est survenue');
        }
    } catch (error) {
        console.error('❌ Erreur lors de l\'ajout au panier:', error);
        alert('Une erreur est survenue lors de l\'ajout au panier');
    }
}

// Fermer la modal en cliquant à l'extérieur
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalBoxPerso');
    if (modal && event.target === modal) {
        closeModalBoxPerso();
    }
});

// Fermer la modal avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModalBoxPerso();
    }
});

// Log au chargement
console.log('✅ box.js chargé avec succès');