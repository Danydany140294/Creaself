// Classe pour gérer plusieurs éléments avec callbacks via IntersectionObserver
class IntersectionObserverList {
  // rootMargin permet de déclencher l'observation un peu avant/après la zone visible
  constructor(rootMargin = "0px") {
    this.mapping = new Map(); // Stocke les éléments et leurs callbacks
    this.observer = new IntersectionObserver(
      (entries) => { // Fonction appelée à chaque changement d'intersection
        for (const entry of entries) {
          const callback = this.mapping.get(entry.target); // Récupère callback pour l'élément
          if (callback) callback(entry.isIntersecting); // Appelle callback avec booléen visibilité
        }
      },
      { rootMargin } // Marge autour de la racine pour déclencher plus tôt ou tard
    );
  }

  // Ajoute un élément à observer avec sa fonction callback
  add(element, callback) {
    this.mapping.set(element, callback);
    this.observer.observe(element); // Démarre l'observation sur l'élément
  }

  // Supprime un élément de l'observation
  remove(element) {
    this.mapping.delete(element);
    this.observer.unobserve(element);
  }

  // Arrête toute observation et vide la liste
  disconnect() {
    this.mapping.clear();
    this.observer.disconnect();
  }
}

// Initialisation de l'observer avec une marge de 300px en haut et bas (pratique pour précharger animations)
const observer = new IntersectionObserverList("300px 0px 300px 0px");

// Sélectionne tous les éléments avec l'attribut data-animate="true"
document.querySelectorAll('[data-animate="true"]').forEach((el) => {
  observer.add(el, (isIntersecting) => {
    if (isIntersecting) {
      el.classList.add("animate-slide-down");  // Ajoute la classe quand visible
    } else {
      el.classList.remove("animate-slide-down"); // Retire la classe quand pas visible
    }
  });
});

// --- Gestion du curseur personnalisé ---

// Sélection des deux éléments ronds du curseur
const outerRing = document.querySelector('.ring--outer');
const innerRing = document.querySelector('.ring--inner');

// Coordonnées initiales du curseur centrées sur la fenêtre
let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;
let currentX = mouseX;
let currentY = mouseY;

// Mise à jour des coordonnées souris à chaque déplacement
document.addEventListener('mousemove', (e) => {
  mouseX = e.clientX;
  mouseY = e.clientY;
});

// Fonction d'animation appelée à chaque frame (~60fps)
function animateCursor() {
  // Interpolation pour un mouvement fluide du cercle extérieur (ralentissement, effet "traînée")
  currentX += (mouseX - currentX) * 0.15; 
  currentY += (mouseY - currentY) * 0.15;

  // Positionne le cercle extérieur avec les coordonnées interpolées
  outerRing.style.left = `${currentX}px`;
  outerRing.style.top = `${currentY}px`;

  // Positionne le cercle intérieur directement sous la souris (sans interpolation)
  innerRing.style.left = `${mouseX}px`;
  innerRing.style.top = `${mouseY}px`;

  // Demande à rappeler cette fonction au prochain frame
  requestAnimationFrame(animateCursor);
}

// Démarre l'animation du curseur
animateCursor();
