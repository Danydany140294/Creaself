/* ========================================
   PRODUITS.JS - PERLINE COOKIES
   Script pour la page produits
   ======================================== */

/* ========================================
   INITIALISATION DU SWIPER
   Doc : https://swiperjs.com/swiper-api
   ======================================== */

const swiper = new Swiper(".nc-swiper", {
    // Configuration de base
    loop: true,                    // Boucle infinie du carrousel
    centeredSlides: true,          // Slide central mis en avant
    slidesPerView: 1.2,            // Nombre de slides visibles (mobile)
    spaceBetween: 15,              // Espace entre les slides (en px)
    grabCursor: true,              // Curseur "main" au survol
    
    // Adaptation responsive
    breakpoints: {
        768: {                     // Tablettes
            slidesPerView: 1.5
        },
        1024: {                    // Desktop
            slidesPerView: 1.8
        }
    },
    
    // Effet 3D type "coverflow"
    effect: "coverflow",
    coverflowEffect: {
        rotate: 12,                // Rotation des slides latéraux
        stretch: 0,                // Étirement horizontal
        depth: 180,                // Profondeur de l'effet 3D
        modifier: 1.4,             // Intensité de l'effet
        slideShadows: false         // Ombres sur les slides
    },
    
    // Pagination (points en bas)
    pagination: {
        el: ".swiper-pagination",
        clickable: true            // Clic sur les points pour naviguer
    },
    
    // Boutons de navigation (flèches)
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
    }
});

/* ========================================
   AJOUT AU PANIER VIA AJAX
   Intercepte les soumissions de formulaire
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    
    // Intercepter tous les formulaires d'ajout au panier
    const forms = document.querySelectorAll('.nc-add-to-cart-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement de la page
            
            const formData = new FormData(form);
            const url = form.action;
            const submitButton = form.querySelector('button[type="submit"]');
            const productName = form.closest('.nc-product-card').querySelector('h3').textContent;
            
            // Désactiver le bouton pendant la requête
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
            
            // Envoi de la requête AJAX
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    // Si Symfony redirige, c'est que l'ajout a réussi
                    showNotification(`✅ "${productName}" ajouté au panier !`, 'success');
                    updateCartCount();
                    
                    // Réactiver le bouton
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
                    
                    // Animation de succès sur la carte
                    const card = form.closest('.nc-product-card');
                    card.classList.add('pulse-success');
                    setTimeout(() => card.classList.remove('pulse-success'), 600);
                    
                } else {
                    throw new Error('Erreur lors de l\'ajout au panier');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('❌ Erreur lors de l\'ajout au panier', 'error');
                
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
            });
        });
    });
});

/* ========================================
   NOTIFICATION TOAST
   Affiche un message temporaire
   ======================================== */

function showNotification(message, type = 'success', duration = 3000) {
    // Retirer les toasts existants
    document.querySelectorAll('.nc-toast').forEach(t => t.remove());
    
    // Créer le nouveau toast
    const toast = document.createElement('div');
    toast.className = `nc-toast nc-toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Styles inline pour le toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: type === 'success' ? 'var(--perline-dore)' : '#e74c3c',
        color: type === 'success' ? 'var(--texte-principal)' : 'white',
        padding: '16px 24px',
        borderRadius: 'var(--radius-md)',
        boxShadow: 'var(--ombre-forte)',
        zIndex: '9999',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        fontWeight: '600',
        animation: 'slideInRight 0.3s ease',
        maxWidth: '400px'
    });
    
    document.body.appendChild(toast);
    
    // Retirer après le délai
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/* ========================================
   MISE À JOUR DU COMPTEUR PANIER
   Récupère le nombre d'articles via API
   ======================================== */

function updateCartCount() {
    fetch('/panier/api/panier', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le badge du panier (si tu en as un dans le header)
            const cartBadge = document.querySelector('.cart-badge, .panier-count');
            if (cartBadge) {
                cartBadge.textContent = data.nombre_articles;
                
                // Animation du badge
                cartBadge.classList.add('bounce');
                setTimeout(() => cartBadge.classList.remove('bounce'), 600);
            }
        }
    })
    .catch(error => console.error('Erreur mise à jour panier:', error));
}

/* ========================================
   GESTION DES QUANTITÉS (+/-)
   Fonction appelée depuis le Twig
   ======================================== */

function updateQty(inputId, delta, min, max) {
    const input = document.getElementById(inputId);
    let currentValue = parseInt(input.value) || min;
    let newValue = currentValue + delta;
    
    // Limiter la valeur entre min et max
    if (newValue >= min && newValue <= max) {
        input.value = newValue;
    }
}

/* ========================================
   SCROLL VERS UN PRODUIT
   Utilisé depuis les slides du Swiper
   ======================================== */

function scrollToProduct(productId) {
    const element = document.getElementById('product-' + productId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Effet de highlight sur le produit
        element.classList.add('highlight');
        setTimeout(() => element.classList.remove('highlight'), 2000);
    }
}

/* ========================================
   SMOOTH SCROLL POUR LES ANCRES
   Navigation fluide dans la page
   ======================================== */

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        // Ignorer les ancres vides ou #
        if (href === '#' || href === '') return;
        
        e.preventDefault();
        const target = document.querySelector(href);
        
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

/* ========================================
   ANIMATION AU SCROLL
   Fade in pour les cartes produits
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.nc-product-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target); // Observer une seule fois
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    cards.forEach(card => {
        observer.observe(card);
    });
});

/* ========================================
   ANIMATIONS CSS DYNAMIQUES
   Ajout des keyframes pour les animations
   ======================================== */

const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    .bounce {
        animation: bounce 0.6s ease;
    }
    
    .pulse-success {
        animation: pulseSuccess 0.6s ease;
    }
    
    @keyframes pulseSuccess {
        0% { transform: scale(1); }
        50% { 
            transform: scale(1.05); 
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.8);
        }
        100% { transform: scale(1); }
    }
    
    .highlight {
        animation: highlight 2s ease;
    }
    
    @keyframes highlight {
        0%, 100% { 
            background: var(--bg-carte);
        }
        50% { 
            background: linear-gradient(135deg, var(--perline-beige), var(--perline-blanc));
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
        }
    }
`;
document.head.appendChild(style);

/* ========================================
   LOG DE DÉMARRAGE
   ======================================== */

console.log('✅ Produits.js Perline chargé avec succès !');
console.log('🍪 Swiper initialisé');
console.log('🛒 AJAX panier activé');