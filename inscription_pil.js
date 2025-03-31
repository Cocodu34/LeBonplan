// Fonction de validation du formulaire
function validateForm() {
    // Cibler les inputs du formulaire
    const title = document.querySelector('select[name="title"]');
    const nom = document.querySelector('input[name="nom"]');
    const prenom = document.querySelector('input[name="prenom"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');

    // Réinitialiser les messages d'erreur
    clearErrors();

    let isValid = true;

    // Vérifier si la civilité est sélectionnée
    if (title.value === '') {
        displayError(title, 'La civilité est requise.');
        isValid = false;
    }

    // Vérifier si le nom est vide
    if (nom.value.trim() === '') {
        displayError(nom, 'Le nom est requis.');
        isValid = false;
    }

    // Vérifier si le prénom est vide
    if (prenom.value.trim() === '') {
        displayError(prenom, 'Le prénom est requis.');
        isValid = false;
    }

    // Vérifier si l'email est valide
    if (email.value.trim() === '') {
        displayError(email, 'L\'email est requis.');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        displayError(email, 'L\'email n\'est pas valide.');
        isValid = false;
    }

    // Vérifier si le mot de passe est valide
    if (password.value.trim() === '') {
        displayError(password, 'Le mot de passe est requis.');
        isValid = false;
    } else if (password.value.length < 6) {
        displayError(password, 'Le mot de passe doit contenir au moins 6 caractères.');
        isValid = false;
    }

    // Activer ou désactiver le bouton en fonction de la validation
    const submitButton = document.getElementById('submit-btn');
    submitButton.disabled = !isValid; // Le bouton sera désactivé si les champs ne sont pas valides

    return isValid;
}

// Fonction pour afficher un message d'erreur
function displayError(input, message) {
    const errorMessage = document.createElement('div');
    errorMessage.classList.add('error');
    errorMessage.innerText = message;

    // Afficher l'erreur sous l'input
    input.parentNode.appendChild(errorMessage);
}

// Fonction pour effacer les messages d'erreur existants
function clearErrors() {
    const errorMessages = document.querySelectorAll('.error');
    errorMessages.forEach((error) => {
        error.remove();
    });
}

// Ajouter l'événement de validation lors de la soumission du formulaire
const form = document.querySelector('form');
form.addEventListener('submit', function(event) {
    if (!validateForm()) {
        event.preventDefault();  // Empêche la soumission si le formulaire est invalide
    }
});
// Gestion du formulaire en AJAX
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêcher le rechargement de la page

    if (!validateForm()) {
        return;
    }

    const formData = new FormData(this);

    fetch('inscription_pilote.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text()) // Récupérer la réponse du serveur
        .then(data => {
            if (data.includes("Inscription réussie")) {
                alert("Inscription réussie !");
                window.location.href = 'accueil_etu.php';

            } else {
                alert(data); // Afficher le message d'erreur reçu du PHP
            }
        })
        .catch(error => console.error('Erreur lors de la requête:', error));
});