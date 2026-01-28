// ========================================
// GESTION DU PANIER - UTILISATEUR CONNECTÉ (BDD)
// ========================================

/**
 * Modifier la quantité d'un article (BDD)
 */
function changeQtyDB(id, type, delta) {
    const row = document.querySelector(`.cart-row[data-id="${id}"]`);
    const input = row.querySelector('input[type="text"]');
    let currentQty = parseInt(input.value);
    
    // Ne pas descendre en dessous de 1
    if (currentQty + delta < 1) return;
    
    // Animation visuelle immédiate
    input.value = currentQty + delta;
    animatePrice(row);
    
    // Appel AJAX vers Symfony
    fetch('/panier/update-quantity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            type: type,
            change: delta
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger la page pour mettre à jour le total
            location.reload();
        } else {
            alert(data.message);
            // Revenir à l'ancienne valeur en cas d'erreur
            input.value = currentQty;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
        input.value = currentQty;
    });
}

/**
 * Supprimer un article du panier (BDD)
 */
function removeItemDB(id, type) {
    if (!confirm('Supprimer cet article du panier ?')) return;
    
    const row = document.querySelector(`.cart-row[data-id="${id}"]`);
    
    // Animation de suppression
    row.style.opacity = '0';
    row.style.transform = 'translateX(50px) scale(0.9)';
    
    // Appel AJAX après l'animation
    setTimeout(() => {
        fetch('/panier/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page
                location.reload();
            } else {
                alert(data.message);
                // Annuler l'animation
                row.style.opacity = '1';
                row.style.transform = 'translateX(0) scale(1)';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
            row.style.opacity = '1';
            row.style.transform = 'translateX(0) scale(1)';
        });
    }, 400);
}

// ========================================
// GESTION DU PANIER - VISITEUR (SESSION)
// ========================================

/**
 * Modifier la quantité d'un article (Session)
 */
function changeQtySession(id, type, delta) {
    const row = document.querySelector(`.cart-row[data-id="${id}"][data-type="${type}"]`);
    const input = row.querySelector('input[type="text"]');
    let currentQty = parseInt(input.value);
    
    // Ne pas descendre en dessous de 1
    if (currentQty + delta < 1) return;
    
    // Animation visuelle immédiate
    input.value = currentQty + delta;
    animatePrice(row);
    
    // Appel AJAX vers Symfony
    fetch('/panier/update-quantity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            type: type,
            change: delta
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger la page pour mettre à jour le total
            location.reload();
        } else {
            alert(data.message);
            input.value = currentQty;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
        input.value = currentQty;
    });
}

/**
 * Supprimer un article du panier (Session)
 */
function removeItemSession(id, type) {
    if (!confirm('Supprimer cet article du panier ?')) return;
    
    const row = document.querySelector(`.cart-row[data-id="${id}"][data-type="${type}"]`);
    
    // Animation de suppression
    row.style.opacity = '0';
    row.style.transform = 'translateX(50px) scale(0.9)';
    
    // Appel AJAX après l'animation
    setTimeout(() => {
        fetch('/panier/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page
                location.reload();
            } else {
                alert(data.message);
                row.style.opacity = '1';
                row.style.transform = 'translateX(0) scale(1)';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
            row.style.opacity = '1';
            row.style.transform = 'translateX(0) scale(1)';
        });
    }, 400);
}

// ========================================
// ANIMATIONS UTILITAIRES
// ========================================

/**
 * Animation du prix lors du changement de quantité
 */
function animatePrice(row) {
    const priceElement = row.querySelector('.total-item-price');
    
    // Flash de couleur rose
    priceElement.style.color = '#ff00cc';
    priceElement.style.transform = 'scale(1.1)';
    
    setTimeout(() => {
        priceElement.style.color = '#1a1a1a';
        priceElement.style.transform = 'scale(1)';
    }, 300);
}

/**
 * Animation smooth scroll vers le résumé (mobile)
 */
function scrollToSummary() {
    const summary = document.querySelector('.summary-card');
    if (summary) {
        summary.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

// ========================================
// INITIALISATION
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Panier chargé avec succès ✨');
    
    // Animation d'entrée des cart-rows
    const cartRows = document.querySelectorAll('.cart-row');
    cartRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});