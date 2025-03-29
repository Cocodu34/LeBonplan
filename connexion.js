// Fonction de validation du formulaire
function validateForm() {
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    const submitButton = document.querySelector('button[type="submit"]');

    // Réinitialiser les messages d'erreur
    clearErrors();

    let isValid = true;

    // Vérifier si l'email est vide ou invalide
    if (email.value.trim() === '') {
        displayError(email, 'L\'email est requis.');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        displayError(email, 'L\'email n\'est pas valide.');
        isValid = false;
    }

    // Vérifier si le mot de passe est vide
    if (password.value.trim() === '') {
        displayError(password, 'Le mot de passe est requis.');
        isValid = false;
    } else if (password.value.length < 6) {
        displayError(password, 'Le mot de passe doit contenir au moins 6 caractères.');
        isValid = false;
    } else if (!/[A-Z]/.test(password.value)) {
        displayError(password, 'Le mot de passe doit contenir au moins une majuscule.');
        isValid = false;
    } else if (!/[0-9]/.test(password.value)) {
        displayError(password, 'Le mot de passe doit contenir au moins un chiffre.');
        isValid = false;
    } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(password.value)) {
        displayError(password, 'Le mot de passe doit contenir au moins un caractère spécial.');
        isValid = false;
    }

    // Activer ou désactiver le bouton en fonction de la validation
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
        alert("Veuillez corriger les erreurs avant de soumettre.");
    }
});

// Ajouter les événements sur les champs de formulaire pour valider en temps réel
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('input', validateForm);
});
