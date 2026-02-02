// ========================================
// CONTACT FORM - Style MIX Design
// ========================================

document.getElementById('future-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.querySelector('.btn-send');
    const btnSpan = btn.querySelector('span');
    
    // Animation d'envoi
    btnSpan.innerHTML = 'ENVOI EN COURS... ✨';
    btn.style.background = 'linear-gradient(135deg, #CCA748, #FF8BA5)';
    btn.style.pointerEvents = 'none';
    
    // Simulation d'envoi avec succès
    setTimeout(() => {
        btnSpan.innerHTML = 'MESSAGE ENVOYÉ ! 💖';
        btn.style.background = 'linear-gradient(135deg, #FF6B8F, #D9B857)';
        
        // Pluie de confettis MIX (rose + gold)
        confettiMixEffect();
        
        // Réinitialisation après 3 secondes
        setTimeout(() => {
            btnSpan.innerHTML = 'ENVOYER L\'ÉTINCELLE';
            btn.style.background = 'linear-gradient(135deg, #CCA748, #FF8BA5)';
            btn.style.pointerEvents = 'auto';
            document.getElementById('future-form').reset();
        }, 3000);
        
    }, 1500);
});

// ========================================
// EFFET CONFETTIS MIX (Rose + Gold)
// ========================================
function confettiMixEffect() {
    const colors = ['#FF6B8F', '#FF8BA5', '#CCA748', '#D9B857', '#F6ECD8'];
    
    for(let i = 0; i < 40; i++) {
        const p = document.createElement('div');
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        const randomSize = Math.random() * 8 + 5; // Entre 5px et 13px
        
        p.style.cssText = `
            position: fixed;
            left: 50%;
            top: 50%;
            width: ${randomSize}px;
            height: ${randomSize}px;
            background: ${randomColor};
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            box-shadow: 0 0 10px ${randomColor}, 0 0 20px ${randomColor};
        `;
        
        document.body.appendChild(p);
        
        // Trajectoires aléatoires
        const destX = (Math.random() - 0.5) * 600;
        const destY = (Math.random() - 0.5) * 600;
        const rotation = Math.random() * 720 - 360;
        
        p.animate([
            { 
                transform: 'translate(0, 0) rotate(0deg) scale(1)', 
                opacity: 1 
            },
            { 
                transform: `translate(${destX}px, ${destY}px) rotate(${rotation}deg) scale(0)`, 
                opacity: 0 
            }
        ], {
            duration: 1200 + Math.random() * 400,
            easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
        });
        
        setTimeout(() => p.remove(), 1600);
    }
}

// ========================================
// ANIMATION DES INPUTS (Optionnel)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.input-group input, .input-group textarea, .input-group select');
    
    inputs.forEach(input => {
        // Animation au focus
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
});