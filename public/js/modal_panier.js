// modal_panier.js - Fichier JavaScript externe

/**
 * Configuration globale
 */
const PANIER_CONFIG = {
    API_ENDPOINTS: {
        get: (window.PANIER_PATHS?.apiBase || '/panier') + '/api/panier',
        updateQty: (window.PANIER_PATHS?.apiBase || '/panier') + '/update-quantity',
        remove: (window.PANIER_PATHS?.apiBase || '/panier') + '/remove'
    },
    PATHS: {
        uploads: window.PANIER_PATHS?.uploadsBase || '/uploads/',
        images: window.PANIER_PATHS?.imagesBase || '/images/'
    },
    FREE_SHIPPING_THRESHOLD: 50.00,
    SHIPPING_COST: 4.90,
    TAX_RATE: 0.07 // 7% TVA
};

/**
 * État global du panier
 */
let panierData = null;

/**
 * Image placeholder en SVG
 */
const PLACEHOLDER_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="80" height="80"%3E%3Crect fill="%23f0f0f0" width="80" height="80"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="12"%3EImage%3C/text%3E%3C/svg%3E';

/**
 * Classe principale pour gérer le panier
 */
class PanierModal {
    constructor() {
        this.modal = document.getElementById('modalPanier');
        this.panierContent = document.getElementById('panierContent');
        this.init();
    }

    /**
     * Initialisation des événements
     */
    init() {
        // Fermeture avec Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Fermeture en cliquant sur l'overlay
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Empêcher la fermeture en cliquant dans la modal
        const modalContent = this.modal?.querySelector('.cart-modal-container');
        modalContent?.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    /**
     * Vérifier si la modal est ouverte
     */
    isOpen() {
        return this.modal && this.modal.style.display === 'flex';
    }

    /**
     * Ouvrir la modal et charger le panier
     */
    async open() {
        if (!this.modal) return;

        this.modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        this.showLoading();
        
        try {
            const data = await this.fetchPanier();
            panierData = data;
            
            if (data.is_empty || data.items.length === 0) {
                this.showEmpty();
            } else {
                this.renderPanier(data);
            }
            
            this.updateSummary(data);
            
        } catch (error) {
            console.error('Erreur chargement panier:', error);
            this.showError();
        }
    }

    /**
     * Fermer la modal
     */
    close() {
        if (!this.modal) return;
        
        this.modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    /**
     * Toggle la modal (ouvrir/fermer)
     */
    toggle() {
        if (this.isOpen()) {
            this.close();
        } else {
            this.open();
        }
    }

    /**
     * Récupérer les données du panier via API
     */
    async fetchPanier() {
        const response = await fetch(PANIER_CONFIG.API_ENDPOINTS.get);
        
        if (!response.ok) {
            throw new Error('Erreur lors du chargement du panier');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }
        
        return data;
    }

    /**
     * Afficher le loading
     */
    showLoading() {
        if (!this.panierContent) return;
        
        this.panierContent.innerHTML = `
            <div class="loading-panier">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Chargement du panier...</p>
            </div>
        `;
    }

    /**
     * Afficher panier vide
     */
    showEmpty() {
        if (!this.panierContent) return;
        
        this.panierContent.innerHTML = `
            <div class="panier-vide">
                <i class="fa-solid fa-basket-shopping fa-3x"></i>
                <h3>Votre panier est vide</h3>
                <p>Ajoutez des produits pour commencer !</p>
            </div>
        `;
    }

    /**
     * Afficher erreur
     */
    showError() {
        if (!this.panierContent) return;
        
        this.panierContent.innerHTML = `
            <div class="panier-vide">
                <i class="fas fa-exclamation-triangle fa-3x"></i>
                <h3>Erreur de chargement</h3>
                <p>Impossible de charger le panier. Veuillez réessayer.</p>
            </div>
        `;
    }

    /**
     * Afficher le contenu du panier
     */
    renderPanier(data) {
        if (!this.panierContent || !data.items || data.items.length === 0) {
            this.showEmpty();
            return;
        }
        
        let itemsHTML = '';
        
        data.items.forEach(item => {
            // Construction du chemin de l'image
            let imageUrl = PLACEHOLDER_IMAGE;

if (item.image) {
    // Déterminer le type de dossier
    let folder = 'produits'; // par défaut pour Cookie
    if (item.type.includes('Box')) {
        folder = 'boxs';
    }
    
    // Construire l'URL complète
    imageUrl = `${PANIER_CONFIG.PATHS.uploads}${folder}/${item.image}`;
    
    // Debug : afficher l'URL construite
    console.log('Image URL construite:', imageUrl);
}
            
            itemsHTML += `
                <div class="cart-card glass-card" data-id="${item.id}" data-type="${item.type}">
                    <div class="item-visual">
                        <img src="${imageUrl}" 
                             alt="${this.escapeHtml(item.nom)}" 
                             onerror="this.src='${PLACEHOLDER_IMAGE}'">
                    </div>
                    <div class="item-info">
                        <h3>${this.escapeHtml(item.nom)}</h3>
                        <span class="item-tag">${item.type}${item.prix_unitaire ? ' • ' + this.formatPrice(item.prix_unitaire) : ''}</span>
                        ${item.composition ? `
                            <div class="panier-item-composition">
                                <small>📦 ${item.composition.map(c => `${c.quantite}x ${this.escapeHtml(c.nom)}`).join(', ')}</small>
                            </div>
                        ` : ''}
                        <div class="qty-control">
                            <button onclick="panierModal.updateQuantity(${item.id}, '${item.type}', -1)">−</button>
                            <span class="qty-val">${item.quantite}</span>
                            <button onclick="panierModal.updateQuantity(${item.id}, '${item.type}', 1)">+</button>
                        </div>
                    </div>
                    <div class="item-actions">
                        <span class="item-price">${this.formatPrice(item.sous_total)}</span>
                        <button class="remove-btn" onclick="panierModal.removeItem(${item.id}, '${item.type}')">
                            <i class="fas fa-trash"></i> RETIRER
                        </button>
                    </div>
                </div>
            `;
        });
        
        this.panierContent.innerHTML = itemsHTML;
        
        // Mettre à jour le compteur d'articles
        this.updateItemsCount(data);
    }

    /**
     * Mettre à jour le compteur d'articles
     */
    updateItemsCount(data) {
        const itemsCount = document.getElementById('itemsCount');
        if (!itemsCount || !data.items) return;
        
        const totalItems = data.items.reduce((sum, item) => sum + item.quantite, 0);
        itemsCount.textContent = `● ${totalItems} Article${totalItems > 1 ? 's' : ''}`;
    }

    /**
     * Mettre à jour le résumé (prix, livraison, etc.)
     */
    updateSummary(data) {
        const subtotal = data.total || 0;
        const shipping = subtotal >= PANIER_CONFIG.FREE_SHIPPING_THRESHOLD ? 0 : PANIER_CONFIG.SHIPPING_COST;
        const tax = subtotal * PANIER_CONFIG.TAX_RATE;
        const total = subtotal + shipping + tax;
        
        // Mettre à jour les valeurs
        this.updateElement('subtotalValue', this.formatPrice(subtotal));
        this.updateElement('shippingValue', shipping === 0 ? 'GRATUIT' : this.formatPrice(shipping));
        this.updateElement('taxValue', this.formatPrice(tax));
        this.updateElement('totalValue', this.formatPrice(total));
        
        // Mettre à jour la barre de progression
        this.updateShippingProgress(subtotal);
    }

    /**
     * Mettre à jour la barre de progression livraison gratuite
     */
    updateShippingProgress(subtotal) {
        const shippingProgress = document.getElementById('shippingProgress');
        if (!shippingProgress) return;
        
        if (subtotal > 0 && subtotal < PANIER_CONFIG.FREE_SHIPPING_THRESHOLD) {
            shippingProgress.style.display = 'block';
            const remaining = PANIER_CONFIG.FREE_SHIPPING_THRESHOLD - subtotal;
            const progress = (subtotal / PANIER_CONFIG.FREE_SHIPPING_THRESHOLD) * 100;
            
            this.updateElement('remainingCost', this.formatPrice(remaining) + ' restant');
            
            const progressFill = document.getElementById('progressFill');
            if (progressFill) {
                progressFill.style.width = progress + '%';
            }
        } else if (subtotal >= PANIER_CONFIG.FREE_SHIPPING_THRESHOLD) {
            shippingProgress.style.display = 'block';
            this.updateElement('remainingCost', '✓ Atteint');
            
            const progressFill = document.getElementById('progressFill');
            if (progressFill) {
                progressFill.style.width = '100%';
            }
        } else {
            shippingProgress.style.display = 'none';
        }
    }

    /**
     * Mettre à jour la quantité d'un article
     */
    async updateQuantity(itemId, itemType, change) {
        try {
            const response = await fetch(PANIER_CONFIG.API_ENDPOINTS.updateQty, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: itemId,
                    type: itemType,
                    change: change
                })
            });
            
            if (!response.ok) {
                throw new Error('Erreur serveur');
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Recharger le panier
                await this.open();
            } else {
                this.showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
            }
        } catch (error) {
            console.error('Erreur updateQuantity:', error);
            this.showNotification('Erreur lors de la mise à jour de la quantité', 'error');
        }
    }

    /**
     * Supprimer un article
     */
    async removeItem(itemId, itemType) {
        if (!confirm('Êtes-vous sûr de vouloir retirer cet article ?')) {
            return;
        }
        
        // Animation de suppression
        const card = document.querySelector(`.cart-card[data-id="${itemId}"][data-type="${itemType}"]`);
        if (card) {
            card.style.transform = 'translateX(50px)';
            card.style.opacity = '0';
            card.style.transition = 'all 0.4s ease';
        }
        
        try {
            const response = await fetch(PANIER_CONFIG.API_ENDPOINTS.remove, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: itemId,
                    type: itemType
                })
            });
            
            if (!response.ok) {
                throw new Error('Erreur serveur');
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Attendre la fin de l'animation
                setTimeout(() => {
                    this.open();
                }, 400);
            } else {
                // Annuler l'animation en cas d'erreur
                if (card) {
                    card.style.transform = '';
                    card.style.opacity = '';
                }
                this.showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            console.error('Erreur removeItem:', error);
            if (card) {
                card.style.transform = '';
                card.style.opacity = '';
            }
            this.showNotification('Erreur lors de la suppression de l\'article', 'error');
        }
    }

    /**
     * Afficher une notification
     */
    showNotification(message, type = 'info') {
        // Implémentation simple avec alert (à améliorer avec un toast)
        alert(message);
    }

    /**
     * Mettre à jour un élément du DOM
     */
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    /**
     * Formater un prix
     */
    formatPrice(price) {
        return parseFloat(price).toFixed(2).replace('.', ',') + ' €';
    }

    /**
     * Échapper le HTML pour éviter les failles XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Instance globale du panier
 */
let panierModal;

/**
 * Initialisation au chargement de la page
 */
document.addEventListener('DOMContentLoaded', function() {
    panierModal = new PanierModal();
    console.log('✅ PanierModal initialisé');
});

/**
 * Fonctions globales pour la compatibilité
 */
function openModalPanier() {
    panierModal?.open();
}

function closeModalPanier() {
    panierModal?.close();
}

function toggleCart() {
    panierModal?.toggle();
}