
  function showAlert() {
    Swal.fire({
      text: "Vous êtes en mode déconnecté.",
      timer: 3000,
      showConfirmButton: false,
      position: "center",
      width: "400px"
    });
  }

  // Afficher l'alerte automatiquement au chargement de la page
  window.onload = function() {
    showAlert();
  };