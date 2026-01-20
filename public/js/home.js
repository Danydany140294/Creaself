// =====================================
// CRUMB PARTICLE EFFECTS
// =====================================
function createCrumbs(x, y) {
    // Utilisation de la palette MIX
    const colors = [
        '#9A6B58',  // mix-cocoa-dark
        '#CCA748',  // mix-gold
        '#B08070',  // mix-cocoa
        '#F6ECD8',  // mix-champagne
        '#FF6B8F'   // mix-rose
    ];
    
    for (let i = 0; i < 15; i++) {
        const crumb = document.createElement('div');
        crumb.className = 'crumb';
        
        const size = Math.random() * 6 + 2;
        crumb.style.width = `${size}px`;
        crumb.style.height = `${size}px`;
        crumb.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        crumb.style.left = `${x}px`;
        crumb.style.top = `${y}px`;
        
        const destinationX = (Math.random() - 0.5) * 200;
        const destinationY = (Math.random() - 0.5) * 200;
        const rotation = Math.random() * 360;
        
        document.body.appendChild(crumb);
        
        const animation = crumb.animate([
            { transform: 'translate(0, 0) rotate(0deg)', opacity: 1 },
            { transform: `translate(${destinationX}px, ${destinationY}px) rotate(${rotation}deg)`, opacity: 0 }
        ], {
            duration: 800 + Math.random() * 400,
            easing: 'cubic-bezier(0, .9, .57, 1)',
            fill: 'forwards'
        });
        
        animation.onfinish = () => crumb.remove();
    }
}

// =====================================
// MAGNETIC BUTTON EFFECT
// =====================================
const magneticBtns = document.querySelectorAll('.js-magnetic-btn');

magneticBtns.forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        
        btn.style.transform = `translate(${x * 0.3}px, ${y * 0.5}px)`;
        btn.parentElement.style.transform = `translate(${x * 0.1}px, ${y * 0.15}px)`;
    });
    
    btn.addEventListener('mouseleave', () => {
        btn.style.transform = `translate(0px, 0px)`;
        btn.parentElement.style.transform = `translate(0px, 0px)`;
    });
});

// =====================================
// TOAST NOTIFICATIONS
// =====================================
const toastContainer = document.getElementById('toast-container');

function showToast(message, icon = 'check_circle') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification glass-card flex items-center gap-4 p-4 rounded-2xl shadow-2xl border-l-4 border-l-or pointer-events-auto min-w-[300px]';
    toast.innerHTML = `
        <div class="bg-or/20 text-or p-2 rounded-xl">
            <span class="material-symbols-outlined">${icon}</span>
        </div>
        <div>
            <p class="text-chocolat font-bold">${message}</p>
            <p class="text-chocolat/50 text-[10px] uppercase font-bold tracking-widest">Délice enregistré</p>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) reverse forwards';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// =====================================
// CUSTOM BOX COMPOSER
// =====================================
let cookieCount = 0;
const maxCookies = 6;
const slots = document.querySelectorAll('.box-slot');
const countDisplay = document.getElementById('cookie-count');
const isoBox = document.getElementById('isometric-box');
const badge = document.getElementById('counter-badge');

function addToBox(name, imgSrc) {
    if (cookieCount < maxCookies) {
        const slot = slots[cookieCount];
        slot.innerHTML = `<img src="${imgSrc}" class="w-[85%] h-[85%] object-cover rounded-full cookie-shadow filling-slot active" alt="${name}">`;
        
        cookieCount++;
        countDisplay.innerText = `${cookieCount}/${maxCookies}`;
        
        // Haptic visual feedback
        isoBox.classList.add('haptic-shake');
        badge.style.transform = 'scale(1.2) rotate(-15deg)';
        
        setTimeout(() => {
            isoBox.classList.remove('haptic-shake');
            badge.style.transform = 'scale(1) rotate(-12deg)';
        }, 400);
        
        showToast(`${name} ajouté au coffret !`, 'cookie');
        createCrumbs(window.innerWidth / 2, window.innerHeight / 2);
    } else {
        showToast("Votre coffret est déjà complet !", "error");
    }
}

// Event listeners for cookie composition
document.querySelectorAll('.js-cookie-item').forEach(item => {
    item.addEventListener('click', (e) => {
        const name = item.dataset.name;
        const img = item.dataset.img;
        addToBox(name, img);
        
        // Crumb effect at click position
        createCrumbs(e.clientX, e.clientY);
    });
});

// =====================================
// ADD TO CART BUTTONS
// =====================================
document.querySelectorAll('.js-add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const name = btn.dataset.name;
        showToast(`${name} ajouté au panier !`, 'shopping_cart');
        createCrumbs(e.clientX, e.clientY);
    });
});

// =====================================
// ORDER BOX BUTTON
// =====================================
const orderBoxBtn = document.getElementById('order-box-btn');
if (orderBoxBtn) {
    orderBoxBtn.addEventListener('click', () => {
        if (cookieCount > 0) {
            showToast("Préparation de votre coffret...", "oven_gen");
        } else {
            showToast("Votre coffret est vide !", "warning");
        }
    });
}