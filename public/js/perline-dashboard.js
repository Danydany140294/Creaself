/**
 * ========================================
 * PERLINE DASHBOARD - JavaScript
 * Gestion des onglets et interactions du tableau de bord utilisateur
 * ========================================
 */

// ========================================
// INITIALISATION GLOBALE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    
    console.log('🍪 Perline Dashboard - Initialisation...');
    
    // Initialiser tous les modules
    initTabSystem();
    initFavoritesSlider();
    initPaymentActions();
    initProductActions();
    initRewardActions();
    initSmoothScroll();
    
    console.log('✨ Dashboard chargé avec succès !');
});

// ========================================
// SYSTÈME D'ONGLETS
// ========================================

/**
 * Initialise le système de navigation par onglets
 */
function initTabSystem() {
    const navLinks = document.querySelectorAll('.nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (!navLinks.length || !tabContents.length) {
        return;
    }
    
    /**
     * Change l'onglet actif
     * @param {string} tabId - ID de l'onglet à activer
     */
    function switchTab(tabId) {
        // Retirer la classe active de tous les liens
        navLinks.forEach(link => link.classList.remove('active'));
        
        // Masquer tous les contenus
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Activer le lien cliqué
        const activeLink = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
        
        // Afficher le contenu correspondant
        const activeContent = document.getElementById(`tab-${tabId}`);
        if (activeContent) {
            activeContent.classList.add('active');
        }
        
        // Mettre à jour l'URL sans recharger la page
        if (history.pushState) {
            history.pushState(null, null, `#${tabId}`);
        }
    }
    
    /**
     * Event listeners sur les liens de navigation
     */
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });
    
    /**
     * Gérer l'onglet initial depuis l'URL
     */
    function initTabFromURL() {
        const hash = window.location.hash.substring(1);
        switchTab(hash || 'profil');
    }
    
    /**
     * Gérer le bouton retour du navigateur
     */
    window.addEventListener('popstate', initTabFromURL);
    
    // Initialiser l'onglet au chargement
    initTabFromURL();
    
    console.log('📊 Système d\'onglets activé');
}

// ========================================
// SLIDER FAVORIS
// ========================================

/**
 * Initialise le slider des produits favoris
 */
function initFavoritesSlider() {
    const favoritesSlider = document.getElementById('favoritesSlider');
    const favPrevBtn = document.getElementById('favPrev');
    const favNextBtn = document.getElementById('favNext');
    
    if (!favoritesSlider || !favPrevBtn || !favNextBtn) {
        return;
    }
    
    // Navigation précédent
    favPrevBtn.addEventListener('click', function() {
        favoritesSlider.scrollBy({
            left: -220,
            behavior: 'smooth'
        });
    });
    
    // Navigation suivant
    favNextBtn.addEventListener('click', function() {
        favoritesSlider.scrollBy({
            left: 220,
            behavior: 'smooth'
        });
    });
    
    // Gérer l'état des boutons selon la position du scroll
    favoritesSlider.addEventListener('scroll', function() {
        const maxScroll = this.scrollWidth - this.clientWidth;
        
        // Bouton précédent
        if (this.scrollLeft <= 0) {
            favPrevBtn.style.opacity = '0.5';
            favPrevBtn.style.cursor = 'not-allowed';
        } else {
            favPrevBtn.style.opacity = '1';
            favPrevBtn.style.cursor = 'pointer';
        }
        
        // Bouton suivant
        if (this.scrollLeft >= maxScroll) {
            favNextBtn.style.opacity = '0.5';
            favNextBtn.style.cursor = 'not-allowed';
        } else {
            favNextBtn.style.opacity = '1';
            favNextBtn.style.cursor = 'pointer';
        }
    });
    
    console.log('❤️ Slider favoris activé');
}

// ========================================
// GESTION DES MOYENS DE PAIEMENT
// ========================================

/**
 * Initialise les actions pour les moyens de paiement
 */
function initPaymentActions() {
    const btnAddCard = document.getElementById('btn-add-card');
    const btnAddApplePay = document.getElementById('btn-add-apple-pay');
    
    if (btnAddCard) {
        btnAddCard.addEventListener('click', function() {
            showNotification('Fonctionnalité à venir : Ajouter une carte Stripe', 'info');
        });
    }
    
    if (btnAddApplePay) {
        btnAddApplePay.addEventListener('click', function() {
            showNotification('Fonctionnalité à venir : Configurer Apple Pay', 'info');
        });
    }
}

/**
 * Définir un moyen de paiement comme défaut
 * @param {number} id - ID du moyen de paiement
 */
function setDefaultPayment(id) {
    if (!confirm('Définir ce moyen de paiement comme défaut ?')) {
        return;
    }
    
    fetch(`/mon-compte/moyens-paiement/definir-defaut/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Moyen de paiement défini par défaut', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Une erreur est survenue', 'error');
    });
}

/**
 * Supprimer un moyen de paiement
 * @param {number} id - ID du moyen de paiement
 */
function deletePayment(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce moyen de paiement ?')) {
        return;
    }
    
    fetch(`/mon-compte/moyens-paiement/supprimer/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Moyen de paiement supprimé', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Une erreur est survenue', 'error');
    });
}

// ========================================
// ACTIONS PRODUITS
// ========================================

/**
 * Initialise les boutons d'ajout de produits au panier
 */
function initProductActions() {
    const addButtons = document.querySelectorAll('.btn-add');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const productCard = this.closest('.product-card');
            const productName = productCard ? productCard.querySelector('.product-name')?.textContent : 'Produit';
            showNotification(`${productName} ajouté au panier !`, 'success');
        });
    });
}

// ========================================
// ACTIONS RÉCOMPENSES & COMMANDES
// ========================================

/**
 * Initialise les actions pour les récompenses et le suivi de commande
 */
function initRewardActions() {
    // Bouton "Suivre ma livraison"
    const trackingBtn = document.querySelector('.btn-full');
    if (trackingBtn && trackingBtn.textContent.includes('Suivre')) {
        trackingBtn.addEventListener('click', function() {
            showNotification('Redirection vers le suivi de commande...', 'success');
            // TODO: Rediriger vers la page de suivi
            // window.location.href = '/suivi-commande';
        });
    }
    
    // Bouton "Utiliser" récompense
    const rewardBtn = document.querySelector('.btn-reward');
    if (rewardBtn) {
        rewardBtn.addEventListener('click', function() {
            showNotification('Récompense appliquée !', 'success');
            // TODO: Déclencher l'application de la récompense
        });
    }
}

// ========================================
// SMOOTH SCROLL
// ========================================

/**
 * Initialise le défilement fluide pour les ancres
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// ========================================
// SYSTÈME DE NOTIFICATIONS
// ========================================

/**
 * Affiche une notification toast
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification (success, error, info, warning)
 */
function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotification = document.querySelector('.perline-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `perline-notification notification-${type}`;
    notification.textContent = message;
    
    // Couleurs selon le type
    const colors = {
        success: { bg: '#dcfce7', text: '#166534' },
        error: { bg: '#fee2e2', text: '#991b1b' },
        warning: { bg: '#fef3c7', text: '#92400e' },
        info: { bg: '#dbeafe', text: '#1e40af' }
    };
    
    const color = colors[type] || colors.info;
    
    // Styles de la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${color.bg};
        color: ${color.text};
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
        max-width: 400px;
        word-wrap: break-word;
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Ajouter les animations CSS pour les notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .perline-notification {
        pointer-events: none;
    }
`;
document.head.appendChild(notificationStyles);

// ========================================
// UTILITAIRES GLOBAUX
// ========================================

/**
 * Formate un prix en euros
 * @param {number} price - Prix à formater
 * @returns {string} Prix formaté
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

/**
 * Formate une date
 * @param {Date|string} date - Date à formater
 * @returns {string} Date formatée
 */
function formatDate(date) {
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    }).format(new Date(date));
}

/**
 * Copie du texte dans le presse-papier
 * @param {string} text - Texte à copier
 */
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showNotification('Copié dans le presse-papier !', 'success');
            })
            .catch(err => {
                console.error('Erreur lors de la copie:', err);
                showNotification('Impossible de copier', 'error');
            });
    } else {
        // Fallback pour les navigateurs plus anciens
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('Copié dans le presse-papier !', 'success');
        } catch (err) {
            console.error('Erreur lors de la copie:', err);
            showNotification('Impossible de copier', 'error');
        }
        
        document.body.removeChild(textArea);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('btn-add-address');
    const btnCancel = document.getElementById('btn-cancel-address');
    const formContainer = document.getElementById('address-form-container');
    const formTitle = document.getElementById('form-title');

    // Afficher le formulaire d'ajout
    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            formContainer.style.display = 'block';
            formTitle.textContent = 'Nouvelle adresse';
            btnAdd.style.display = 'none';
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    // Cacher le formulaire
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            formContainer.style.display = 'none';
            btnAdd.style.display = 'inline-block';
            const form = formContainer.querySelector('form');
            if (form) form.reset();
        });
    }

    // Gérer la suppression d'adresse avec confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete-address');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const addressName = this.dataset.addressName;
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'adresse "${addressName}" ?`)) {
                window.location.href = this.href;
            }
        });
    });
});

// ========================================
// EXPORTS GLOBAUX
// ========================================

// Rendre les fonctions disponibles globalement
window.showNotification = showNotification;
window.setDefaultPayment = setDefaultPayment;
window.deletePayment = deletePayment;
window.formatPrice = formatPrice;
window.formatDate = formatDate;
window.copyToClipboard = copyToClipboard;

// ========================================
// FIN DU FICHIER
// ========================================