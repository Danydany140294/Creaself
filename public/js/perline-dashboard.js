/**
 * PERLINE DASHBOARD - JavaScript
 * Gestion des onglets et interactions
 */

// ==========================================
// SYSTÈME D'ONGLETS
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Récupérer tous les liens de navigation
    const navLinks = document.querySelectorAll('.nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');
    
    /**
     * Fonction pour changer d'onglet
     */
    function switchTab(tabId) {
        // Retirer la classe active de tous les liens
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Masquer tous les contenus
        tabContents.forEach(content => {
            content.classList.remove('active');
        });
        
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
        const hash = window.location.hash.substring(1); // Enlever le #
        
        if (hash) {
            // Si un hash existe, activer cet onglet
            switchTab(hash);
        } else {
            // Sinon, activer le premier onglet par défaut
            switchTab('profil');
        }
    }
    
    /**
     * Gérer le bouton retour du navigateur
     */
    window.addEventListener('popstate', function() {
        initTabFromURL();
    });
    
    // Initialiser l'onglet au chargement
    initTabFromURL();
    
    
    // ==========================================
    // SLIDER FAVORIS
    // ==========================================
    const favoritesSlider = document.getElementById('favoritesSlider');
    const favPrevBtn = document.getElementById('favPrev');
    const favNextBtn = document.getElementById('favNext');
    
    if (favoritesSlider && favPrevBtn && favNextBtn) {
        favPrevBtn.addEventListener('click', function() {
            favoritesSlider.scrollBy({
                left: -220,
                behavior: 'smooth'
            });
        });
        
        favNextBtn.addEventListener('click', function() {
            favoritesSlider.scrollBy({
                left: 220,
                behavior: 'smooth'
            });
        });
    }
    
    
    // ==========================================
    // NOTIFICATIONS (optionnel)
    // ==========================================
    
    /**
     * Fonction pour afficher une notification
     */
    function showNotification(message, type = 'success') {
        // Supprimer les notifications existantes
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Styles de la notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#22c55e' : '#ef4444'};
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Ajouter les animations pour les notifications
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
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
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Export de la fonction pour utilisation globale
    window.showNotification = showNotification;
    
    
    // ==========================================
    // BOUTONS "AJOUTER" DES PRODUITS
    // ==========================================
    const addButtons = document.querySelectorAll('.btn-add');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const productName = this.closest('.product-card').querySelector('.product-name').textContent;
            showNotification(`${productName} ajouté au panier !`, 'success');
        });
    });
    
    
    // ==========================================
    // BOUTON "SUIVRE MA LIVRAISON"
    // ==========================================
    const trackingBtn = document.querySelector('.btn-primary');
    if (trackingBtn) {
        trackingBtn.addEventListener('click', function() {
            showNotification('Redirection vers le suivi de commande...', 'success');
            // Ici vous pouvez rediriger vers la page de suivi
            // window.location.href = '/suivi-commande';
        });
    }
    
    
    // ==========================================
    // BOUTON "UTILISER" RÉCOMPENSE
    // ==========================================
    const rewardBtn = document.querySelector('.btn-reward');
    if (rewardBtn) {
        rewardBtn.addEventListener('click', function() {
            showNotification('Récompense appliquée !', 'success');
            // Ici vous pouvez déclencher l'application de la récompense
        });
    }
    
    
    // ==========================================
    // SMOOTH SCROLL POUR LES ANCRES
    // ==========================================
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
    
    
    // ==========================================
    // DÉTECTION DU SCROLL DANS LE SLIDER
    // ==========================================
    if (favoritesSlider) {
        // Désactiver les boutons si on est au début/fin
        favoritesSlider.addEventListener('scroll', function() {
            const maxScroll = this.scrollWidth - this.clientWidth;
            
            if (this.scrollLeft <= 0) {
                favPrevBtn.style.opacity = '0.5';
                favPrevBtn.style.cursor = 'not-allowed';
            } else {
                favPrevBtn.style.opacity = '1';
                favPrevBtn.style.cursor = 'pointer';
            }
            
            if (this.scrollLeft >= maxScroll) {
                favNextBtn.style.opacity = '0.5';
                favNextBtn.style.cursor = 'not-allowed';
            } else {
                favNextBtn.style.opacity = '1';
                favNextBtn.style.cursor = 'pointer';
            }
        });
    }
    
    
    // ==========================================
    // CONSOLE LOG DE DÉMARRAGE
    // ==========================================
    console.log('🍪 Perline Dashboard chargé avec succès !');
    console.log('📊 Système d\'onglets actif');
    console.log('✨ Toutes les interactions sont prêtes');
});


// ==========================================
// UTILITAIRES GLOBAUX
// ==========================================

/**
 * Fonction pour formater un prix
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

/**
 * Fonction pour formater une date
 */
function formatDate(date) {
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    }).format(new Date(date));
}

/**
 * Fonction pour copier dans le presse-papier
 */
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            window.showNotification('Copié dans le presse-papier !', 'success');
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
        });
    }
}

// Export des utilitaires pour utilisation globale
window.formatPrice = formatPrice;
window.formatDate = formatDate;
window.copyToClipboard = copyToClipboard;