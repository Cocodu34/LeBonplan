document.addEventListener("DOMContentLoaded", function () {
    fetch("postuler.php")
        .then(response => response.json())
        .then(data => {
            if (!data || Object.keys(data).length === 0) {
                console.log("Formulaire vide (rôle pilote ou aucun utilisateur connecté)");
                return;
            }

            document.getElementById("nom").value = data.nom;
            document.getElementById("prenom").value = data.prenom;
            document.getElementById("couriel").value = data.email;

            const selectSexe = document.getElementById("sexe");
            for (let i = 0; i < selectSexe.options.length; i++) {
                if (selectSexe.options[i].value === data.civilite) {
                    selectSexe.selectedIndex = i;
                    break;
                }
            }
        })
        .catch(error => {
            console.error("Erreur AJAX :", error);
        });
});
