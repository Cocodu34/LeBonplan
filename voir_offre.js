function verifierInscription() {
    console.log("js demare");
    fetch("./get_role.php") // ou "../get_role.php" selon ta structure
    .then(response => {
        if (!response.ok) {
            throw new Error("Erreur serveur");
        }
        return response.json();
    })
    .then(data => {
        const role = data.role;
 
        if (role === "pilote") {
            // Afficher le popup spécifique pilote
            Swal.fire({
                title: "Vous êtes connecté en tant que pilote. Souhaitez-vous découvrir l'annonce et y postuler ?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Découvrir",
                cancelButtonText: "Retour",
                confirmButtonColor: "#2368e1",
                cancelButtonColor: "#d33",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "postuler.html";
                }
            });
        } else if (role === "etudiant") {
            // Accès direct à la page postuler
            window.location.href = "postuler.html";
        } else {
            // Non connecté ou rôle inconnu
            Swal.fire({
                icon: "warning",
                title: "Vous devez être connecté pour postuler.",
                showConfirmButton: true
            });
        }
    })
    .catch(error => {
        console.error("Erreur AJAX :", error);
        Swal.fire({
            icon: "error",
            title: "Erreur serveur",
            text: "Impossible de vérifier votre rôle."
        });
    });
}
 
// Ajouter l'écouteur sur le bouton
document.addEventListener("click", function (event) {
    if (event.target && event.target.id === "verifier-btn") {
        verifierInscription();
    }
});