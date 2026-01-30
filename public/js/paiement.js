// ========================================
// GESTION DE LA MODAL DE SUCCÈS
// ========================================

/**
 * Afficher la modal de succès
 */
function showSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Ajouter une classe pour l'animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
}

/**
 * Fermer la modal (optionnel)
 */
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Fermer la modal si on clique en dehors (optionnel)
document.addEventListener('click', function(event) {
    const modal = document.getElementById('successModal');
    if (modal && event.target === modal) {
        // Décommentez si vous voulez permettre de fermer en cliquant dehors
        // closeSuccessModal();
    }
});

// Afficher la modal automatiquement au chargement si elle existe
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('successModal');
    if (modal && modal.style.display !== 'none') {
        // La modal est déjà visible, l'animer
        setTimeout(() => {
            modal.classList.add('show');
        }, 100);
    }
});