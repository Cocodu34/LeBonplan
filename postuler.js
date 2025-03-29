
// Cibler le bouton hamburger et le menu de gauche
const menuToggle = document.getElementById('menu-toggle');
const mainMenu = document.getElementById('main-menu');

// Ajouter un événement au clic pour afficher/masquer le menu
menuToggle.addEventListener('click', function() {
  mainMenu.classList.toggle('active'); // Ajouter ou retirer la classe 'active' pour ouvrir/fermer
});

function scrollToBottom() {
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth'
    });
}