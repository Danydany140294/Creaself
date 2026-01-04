// Effet interactif sur le bouton LOGIN
document.querySelector('.enter').addEventListener('mouseenter', function() {
    document.querySelectorAll('.floating-elements div').forEach(function(bubble) {
        bubble.style.animationDuration = '8s';
    });
});

document.querySelector('.enter').addEventListener('mouseleave', function() {
    document.querySelectorAll('.floating-elements div').forEach(function(bubble, index) {
        bubble.style.animationDuration = (12 + index) + 's';
    });
});

// Effet sur le bouton BACK
const backBtn = document.querySelector('.back-btn');

if (backBtn) {
    backBtn.addEventListener('mouseenter', function() {
        document.querySelectorAll('.floating-elements div').forEach(function(bubble) {
            bubble.style.animationDuration = '6s';
        });
    });

    backBtn.addEventListener('mouseleave', function() {
        document.querySelectorAll('.floating-elements div').forEach(function(bubble, index) {
            bubble.style.animationDuration = (12 + index) + 's';
        });
    });
}