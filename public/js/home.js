// =====================================
// CRUMB PARTICLE EFFECTS
// =====================================
function createCrumbs(x, y) {
    const colors = ['#9A6B58','#CCA748','#B08070','#F6ECD8','#FF6B8F'];

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
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = 'toast-notification glass-card flex items-center gap-4 p-4 rounded-2xl shadow-2xl border-l-4 border-l-or';

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
        toast.remove();
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

function addToBox(name, imgSrc) {

    if (cookieCount >= maxCookies) {
        showToast("Votre coffret est déjà complet !", "error");
        return;
    }

    const slot = slots[cookieCount];

    slot.innerHTML = `
        <img src="${imgSrc}" 
             class="w-[85%] h-[85%] object-cover rounded-full cookie-shadow filling-slot active" 
             alt="${name}">
    `;

    cookieCount++;
    countDisplay.innerText = `${cookieCount}/${maxCookies}`;

    // 🔥 SCROLL AUTO MOBILE (FIX UX)
    if (window.innerWidth < 768) {
        isoBox.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }

    showToast(`${name} ajouté au coffret !`, 'cookie');
}

// CLICK ITEMS
document.querySelectorAll('.js-cookie-item').forEach(item => {
    item.addEventListener('click', (e) => {
        addToBox(item.dataset.name, item.dataset.img);
        createCrumbs(e.clientX, e.clientY);
    });
});

// =====================================
// ORDER BUTTON
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

