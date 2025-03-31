// Fonction de validation du formulaire
function validateForm() {
    // Cibler les inputs du formulaire
    const nom = document.querySelector('input[name="nom"]');
    const prenom = document.querySelector('input[name="prenom"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');

    // Réinitialiser les messages d'erreur
    clearErrors();

    let isValid = true;

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
    } else {
    }
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("Script.js chargé");

    //  Bouton "Téléverser" → Envoi du fichier à CV.php sans validation
    document.getElementById("upload-btn").addEventListener("click", function (event) {
        event.preventDefault(); // Empêche toute soumission du formulaire principal

        let fileInput = document.getElementById("file");
        let file = fileInput.files[0];
        let messageContainer = document.getElementById("upload-message");

        if (!file) {
            messageContainer.innerHTML = '<span style="color: white;">Veuillez sélectionner un fichier.</span>';
            return;
        }

        let formData = new FormData();
        formData.append("file", file);

        fetch("CV.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                messageContainer.innerHTML = `<span style="color: white;">${data.error}</span>`;
            } else {
                messageContainer.innerHTML = `<span style="color: white;">${data.success}</span>`;
            }
        })
        .catch(error => {
            messageContainer.innerHTML = '<span style="color: white;">Erreur lors du téléversement.</span>';
            console.error(error);
        });
    });

    // Bouton "C'est parti !" → Exécute la validation du formulaire
    document.getElementById("submit-btn").addEventListener("click", function (event) {
        console.log("Bouton 'C'est parti !' cliqué");
        function validateForm() {
            // Cibler les inputs du formulaire
            const nom = document.querySelector('input[name="nom"]');
            const prenom = document.querySelector('input[name="prenom"]');
            const email = document.querySelector('input[name="email"]');
            const password = document.querySelector('input[name="password"]');
        
            // Réinitialiser les messages d'erreur
            clearErrors();
        
            let isValid = true;
        
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
                
            } else {
            }
        })
        // Gestion du formulaire en AJAX
        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher le rechargement de la page

            if (!validateForm()) {
                alert("Veuillez corriger les erreurs avant de soumettre.");
                return;
            }

            const formData = new FormData(this);

            fetch('inscription_etu.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) // Récupérer la réponse du serveur
            .then(data => {
                if (data.includes("Inscription réussie")) {
                    alert("Inscription réussie !");
                    window.location.href = 'accueil_etu.php'; // Rediriger vers la page d'accueil

                } else {
                    alert(data); // Afficher le message d'erreur reçu du PHP
                }
            })
            .catch(error => console.error('Erreur lors de la requête:', error));
        });
    })
});









