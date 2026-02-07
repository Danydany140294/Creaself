/**
 * NAVBAR - Gestion du menu de navigation
 * @version 2.0
 */

(function() {
    'use strict';
    
    // ========================================
    // VARIABLES
    // ========================================
    const elements = {
        navLinks: document.getElementById('navLinks'),
        burgerMenu: document.getElementById('burgerMenu'),
        navOverlay: document.getElementById('navOverlay'),
        userMenu: document.getElementById('userMenu'),
        btnPanier: document.getElementById('btnPanier'),
        cartBadge: document.getElementById('cartBadge'),
        modalPanier: document.getElementById('modalPanier')
    };
    
    // ========================================
    // MENU MOBILE
    // ========================================
    function toggleMobileMenu() {
        const isActive = elements.navLinks.classList.toggle('active');
        elements.burgerMenu.classList.toggle('active');
        elements.navOverlay.classList.toggle('active');
        elements.burgerMenu.setAttribute('aria-expanded', isActive);
        
        // Bloquer le scroll du body quand le menu est ouvert
        document.body.style.overflow = isActive ? 'hidden' : '';
    }
    
    function closeMobileMenu() {
        elements.navLinks.classList.remove('active');
        elements.burgerMenu.classList.remove('active');
        elements.navOverlay.classList.remove('active');
        elements.burgerMenu.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
    
    // ========================================
    // MENU UTILISATEUR DESKTOP
    // ========================================
    function toggleUserMenu(e) {
        e.stopPropagation();
        if (!elements.userMenu) return;
        
        const isActive = elements.userMenu.classList.toggle('active');
        const btn = elements.userMenu.querySelector('.btn-user');
        if (btn) btn.setAttribute('aria-expanded', isActive);
    }
    
    function closeUserMenu() {
        if (!elements.userMenu) return;
        elements.userMenu.classList.remove('active');
        const btn = elements.userMenu.querySelector('.btn-user');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }
    
    // ========================================
    // MODAL PANIER
    // ========================================
    function openModalPanier() {
        if (!elements.modalPanier) {
            console.warn('⚠️ Modal panier introuvable');
            return;
        }
        
        elements.modalPanier.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        chargerPanier();
    }
    
    function closeModalPanier() {
        if (!elements.modalPanier) return;
        elements.modalPanier.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // ========================================
    // API PANIER
    // ========================================
    async function chargerPanier() {
        try {
            const response = await fetch('/panier/api/panier');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                updateCartBadge(data.nombre_articles || 0);
            }
        } catch (error) {
            console.error('❌ Erreur chargement panier:', error);
            updateCartBadge(0);
        }
    }
    
    function updateCartBadge(count) {
        if (!elements.cartBadge) return;
        
        elements.cartBadge.textContent = count;
        elements.cartBadge.classList.toggle('show', count > 0);
    }
    
    // ========================================
    // EVENT LISTENERS
    // ========================================
    function initEventListeners() {
        // Burger menu
        if (elements.burgerMenu) {
            elements.burgerMenu.addEventListener('click', toggleMobileMenu);
        }
        
        // Overlay
        if (elements.navOverlay) {
            elements.navOverlay.addEventListener('click', closeMobileMenu);
        }
        
        // Liens du menu mobile - fermeture auto
        if (elements.navLinks) {
            const links = elements.navLinks.querySelectorAll('a:not(.mobile-login-btn):not(.mobile-user-links a)');
            links.forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });
        }
        
        // Menu utilisateur desktop
        if (elements.userMenu) {
            const btn = elements.userMenu.querySelector('.btn-user');
            if (btn) {
                btn.addEventListener('click', toggleUserMenu);
            }
        }
        
        // Fermer user menu en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (elements.userMenu && !elements.userMenu.contains(e.target)) {
                closeUserMenu();
            }
        });
        
        // Bouton panier
        if (elements.btnPanier) {
            elements.btnPanier.addEventListener('click', openModalPanier);
        }
        
        // Fermer modal panier en cliquant dehors
        if (elements.modalPanier) {
            elements.modalPanier.addEventListener('click', (e) => {
                if (e.target === elements.modalPanier) {
                    closeModalPanier();
                }
            });
        }
        
        // Touche Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMobileMenu();
                closeUserMenu();
                closeModalPanier();
            }
        });
        
        // Resize - fermer le menu mobile si passage en desktop
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            }, 250);
        });
    }
    
    // ========================================
    // INITIALISATION
    // ========================================
    function init() {
        console.log('✅ Navbar initialisée');
        console.log('📱 Largeur écran:', window.innerWidth + 'px');
        
        initEventListeners();
        chargerPanier();
    }
    
    // Démarrage au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Exposer closeModalPanier globalement pour le bouton de fermeture dans le modal
    window.closeModalPanier = closeModalPanier;
    
})();