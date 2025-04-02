// Fonction de validation du formulaire
function validateForm() {
    // Cibler les inputs du formulaire
    const lastname = document.querySelector('input[name="lastname"]');
    const surname = document.querySelector('input[name="surname"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');

    // Réinitialiser les messages d'erreur
    clearErrors();

    let isValid = true;

    // Vérifier si le nom est vide
    if (lastname.value.trim() === '') {
        displayError(lastname, 'Le nom est requis.');
        isValid = false;
    }

    // Vérifier si le prénom est vide
    if (surname.value.trim() === '') {
        displayError(surname, 'Le prénom est requis.');
        isValid = false;
    }

    // Vérifier si l'email est vide ou mal formé
    if (email.value.trim() === '') {
        displayError(email, 'L\'email est requis.');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        displayError(email, 'L\'email n\'est pas valide.');
        isValid = false;
    }

    // Vérifier si le mot de passe est vide ou trop court
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
    // Si le formulaire n'est pas valide, empêcher la soumission
    if (!validateForm()) {
        event.preventDefault();
        alert("Veuillez corriger les erreurs avant de soumettre."); // Affiche un pop-up d'erreur
    }
});

// Ajouter les événements sur les champs de formulaire pour valider en temps réel
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('input', validateForm);
});

// Ajouter un événement au bouton de soumission pour éviter le rechargement de la page et effectuer la redirection
const submitButton = document.getElementById('submit-btn');
submitButton.addEventListener('click', function(event) {
    event.preventDefault(); // Empêche la soumission par défaut

    if (validateForm()) {  // Si le formulaire est valide, rediriger
        window.location.href = 'accueil_etu.html'; // Rediriger vers la page d'accueil
    } else {
        alert("Veuillez corriger les erreurs avant de soumettre."); // Afficher un message d'erreur
    }
});
