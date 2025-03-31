// Fonction de validation du formulaire
function validateForm() {
    // Cibler les inputs du formulaire
    const entreprise = document.querySelector('input[name="entreprise"]');
    const nom = document.querySelector('input[name="nom"]');
    const prenom = document.querySelector('input[name="prenom"]');
    const email = document.querySelector('input[name="email"]');
    const address = document.querySelector('input[name="address"]');
    const SIRET = document.querySelector('input[name="SIRET"]');
    const SIREN = document.querySelector('input[name="SIREN"]');
    const domaine = document.querySelector('input[name="domaine"]');
    const password = document.querySelector('input[name="password"]');

    // Réinitialiser les messages d'erreur
    clearErrors();

    let isValid = true;

    // Vérifier si le nom de l'entreprise est vide
    if (entreprise.value.trim() === '') {
        displayError(entreprise, 'Le nom de l\'entreprise est requis.');
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

    // Vérifier si l'adresse est vide
    if (address.value.trim() === '') {
        displayError(address, 'L\'adresse est requise.');
        isValid = false;
    }


    // Vérifier si le numéro SIRET est valide
    if (SIRET.value.trim() === '') {
        displayError(SIRET, 'Le numéro de SIRET est requis.');
        isValid = false;
    } else if (!/^\d{9}$/.test(SIRET.value)) { // Vérification du format du SIRET (9 chiffres)
        displayError(SIRET, 'Le numéro de SIRET doit être composé de 9 chiffres.');
        isValid = false;
    }

    // Vérifier si le numéro SIREN est valide
    if (SIREN.value.trim() === '') {
        displayError(SIREN, 'Le numéro de SIREN est requis.');
        isValid = false;
    }

    // Vérifier si le domaine d'activité est vide
    if (domaine.value.trim() === '') {
        displayError(domaine, 'Le domaine d\'activité est requis.');
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

// Ajouter les événements sur les champs de formulaire pour valider en temps réel
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('input', validateForm);
});

// Ajouter un événement au bouton de soumission
const submitButton = document.getElementById('submit-btn');
submitButton.addEventListener('click', function(event) {
    event.preventDefault();  // Empêche la soumission par défaut du formulaire

    if (validateForm()) {
        // Création de l'objet FormData pour envoyer les données du formulaire
        const formData = new FormData(document.querySelector('form'));

        // Envoi des données via AJAX avec fetch()
        fetch('inscription_ent.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())  // On récupère la réponse du serveur
            .then(data => {
                // Si l'inscription est réussie
                if (data.includes("Inscription réussie")) {
                    alert("Inscription réussie ! Bienvenue !");
                    window.location.href = 'accueil_ent.php';  // Si tout va bien, redirige vers la page entreprise
                } else {
                    alert(data);  // Sinon, afficher l'erreur reçue du serveur
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'envoi du formulaire :', error);
                alert("Une erreur est survenue. Veuillez réessayer.");
            });
    } else {
    }
});