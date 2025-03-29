document.querySelector(".btn-delete").addEventListener("click", () => {
    const titre = document.getElementById("Offre").textContent.trim();
    const contenu = document.getElementById("description").textContent.trim();

    fetch("creer_annonce.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            titre: titre,
            contenu: contenu
        })
    })
        .then(response => response.text())
        .then(data => {
            alert(data);
            window.location.href = "offre_ent.html";
        })
        .catch(err => {
            console.error("Erreur : ", err);
            alert("Erreur lors de la publication de l'offre.");
        });
});
