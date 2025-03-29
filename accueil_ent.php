<?php
header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$dbname = "stage";
$username = "root";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ⚠️ Si on reçoit une requête AJAX de recherche, on retourne uniquement les résultats
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
   
    $query = "SELECT 
    u.nom,
    u.prenom,
    u.descriptif,
    e.etablissement AS etablissement,
    ent.domaine_activite AS entreprise
FROM Utilisateur u
LEFT JOIN Etudiant e ON u.Id_uti = e.Id_uti
LEFT JOIN Entreprise ent ON u.Id_uti = ent.Id_uti
WHERE 
    u.nom LIKE :search
    OR u.prenom LIKE :search
    OR u.descriptif LIKE :search
    OR e.etablissement LIKE :search
    OR ent.domaine_activite LIKE :search;";
   
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($annonces) > 0) {
        foreach ($annonces as $annonce) {
            echo '<div class="annonce">';
            echo '  <div class="box">';
            echo '    <h2 class="annonce-title">' . htmlspecialchars($annonce['nom']) .' '.  htmlspecialchars($annonce['prenom']) .'</h2>';
            if (!empty($annonce['etablissement'])) {
    		echo '<h3 class="annonce-title" > Établissement : ' . htmlspecialchars($annonce['etablissement']).'</h3>';
	    } elseif (!empty($annonce['entreprise'])) {
    		echo '<h3 class="annonce-title" > Société : ' .  htmlspecialchars($annonce['entreprise']).'</h3>';
	    }
            echo '    <p class="annonce-mode">' . htmlspecialchars($annonce['descriptif']) . '</p>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucun profil trouvé.</p>';
    }

    exit; // 👈 important : empêche d'afficher tout le HTML ci-dessous
}
?>


<!doctype html> 
<html lang="fr"> 
   <head> 
      <meta charset="utf-8">
      <meta name="description" content="Postuler à une offre de stage">
      <title>Lebonplan</title>
      <link rel="stylesheet" href="accueil.css">
      <link rel="icon" href="./images/logo_chap.png">
      <style>

    .container {
    display: flex;
    flex-direction: column;   /* Boîtes les unes au-dessus des autres */
    align-items: center;      /* Centre les .box horizontalement */
    width: 100%;
    padding: 20px;
    margin: 0 auto;
    gap: 20px;                /* Espace entre les boîtes */
    background-color: white;
}
.box {
    width: 80vw;                        /* Largeur de chaque boîte */
    background-color: rgb(184, 184, 184);
    color: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.2);
    box-sizing: border-box;
}
</style>
    </head> 
    <body>
    <header style="text-align: center; padding: 20px;">
        <img src="./images/logo.png" alt="Logo" style="width: 500px;"> 
    </header>
    <header>
        <div class="navbar">
            <!-- Bouton hamburger -->
            <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu">&#9776;</button>
            
            <nav class="nav-items-container">
                <ul class="main-menu" id="main-menu">
                    <li class="menu-item"><a href="./accueil_ent.HTML" class="top-level-entry active">Accueil</a></li>
                    <li class="menu-item"><a href="./contact_ent.HTML" class="top-level-entry">Contact</a></li>
                    <li class="menu-item"><a href="./entreprise.html" class="top-level-entry">Entreprise</a></li>
                    <li class="menu-item"><a href="./offre_ent.html" class="top-level-entry">Offre</a></li>
                </ul>

                <!-- Liens de Connexion et S'inscrire à droite -->
                <div class="auth-links">
                    <a href="./accueil.html" class="button">Déconnexion</a>
                </div>
            </nav>
        </div>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./accueil_ent.HTML">Accueil</a></li>
            </ol>
        </nav>
        <br>
     </header>
     <main style=" background-color: #f9f9f9;">
	
	<!-- Barre de recherche -->
	<br>
	<br>
	<div class="search-container" style="text-align:center; margin:20px; background-color: #f9f9f9;">
  		<input type="text" id="search-input" class="search-input" placeholder="Rechercher un nom ou prénom de profil">
  		<button type="button" class="search-button" onclick="rechercherOffres()">Rechercher</button>
	</div>


		<!-- Conteneur des annonces -->
	<div class="container" id="annonces-container" style=" background-color: #f9f9f9;">
  	<!-- Les résultats AJAX apparaîtront ici -->
	</div>

	<!-- Script JS AJAX -->
	<script>
  	function rechercherOffres() {
      		const input = document.getElementById("search-input").value.trim();

      // 👉 Si le champ est vide, on efface les résultats et on sort
      		if (input === "") {
          		document.getElementById("annonces-container").innerHTML = "";
          		return;
      		}

      		const xhr = new XMLHttpRequest();
      		xhr.open("GET", window.location.pathname + "?search=" + encodeURIComponent(input), true);
      		xhr.onreadystatechange = function () {
          		if (xhr.readyState === 4 && xhr.status === 200) {
              			document.getElementById("annonces-container").innerHTML = xhr.responseText;
          		}
      		};
      		xhr.send();
  		}

  		// Rechercher à chaque frappe (optionnel mais fluide)
  		document.getElementById("search-input").addEventListener("keyup", function () {
      		rechercherOffres();
  	});
	</script>
        <!-- Job Listings Section -->
        <section class="job-listings">
          <h2>Offres récentes</h2>
          <div class="jobs-grid">
            <article class="job-card">
              <h3>Technicien en électricité - Stage 8 semaines</h3>
            </article>
            <article class="job-card">
              <h3>Boulanger patissier - Alternance 2 ans</h3>
            </article>
            <article class="job-card">
              <h3>Développeur Web - Stage 6 mois</h3>
            </article>
            <article class="job-card">
              <h3>Graphiste - Alternance 1 an</h3>
            </article>
          </div>
        </section>
      
        <!-- Hero Section (Text Below Job Listings) -->
        <section class="hero">
          <div class="hero-content">
            <p>Vous recherchez un Stage ou une Alternance ?</p>
            <h1>LeBonPlan est là pour vous aider !</h1>
            <p>Avec plus de 15 millions d’élèves inscrits et 45 000 entreprises référencées, vous trouverez forcément une annonce qui vous correspond.</p>
          </div>
        </section>
      </main>
           
</body> 
<footer class="footer">
  <div class="footer-container">
    <!-- Colonne 1 : Logos -->
    <div class="footer-column">
      <img src="images/logo_chap.png" alt="Logo principal" class="footer-logo">
    </div>

    <!-- Colonne 2 : Coordonnées -->
    <div class="footer-column">
      <h3>Coordonnées</h3>
      <a  style='color:#ffffff'href="https://www.google.fr/maps/place/Campus+CESI/@43.5792319,3.9432547,794m/data=!3m2!1e3!4b1!4m6!3m5!1s0x12b6afdaa52cccbf:0xa4dd1993e0746bd!8m2!3d43.5792281!4d3.9481256!16s%2Fg%2F1v202y6s?entry=ttu&g_ep=EgoyMDI1MDIwMi4wIKXMDSoASAFQAw%3D%3D">Immeuble Le Quatrième Zone Aéroportuaire de Montpellier Méditerranée, 34130 Mauguio</a>
      <p><i class="fa-solid fa-envelope"></i> contact@cesi.fr</p>
      <p><i class="fa-solid fa-phone"></i> +33 6 12 34 56 78</p>
    </div>

    <!-- Colonne 3 : Navigation -->
    <div class="footer-column">
      <h3>Navigation</h3>
      <ul class="footer-links">
        <li><a href="./coockies_ent.html">Cookies</a></li>
        <li><a href="./faq_ent.html">F.A.Q</a></li>
        <li><a href="./cgu_ent.html">Conditions générales</a></li>
        <li><a href="./protection_ent.html">Politique de protection des données</a></li>
        <li><a href="./mentions_legales_ent.html">Mentions légales</a></li>
      </ul>
    </div>

    <!-- Colonne 4 : Réseaux sociaux -->
    <div class="footer-column">
      <h3>Suivez-nous</h3>
      <div class="social-buttons">
        <a class="social-button twitter" href="https://x.com/cesi_officiel?s=21" target="_blank"><i class="fa-brands fa-twitter">
          <img class="twitter" 
                      src="./images/Twitter.png"></i></a>
          <a class="social-button tiktok" href=" https://www.tiktok.com/@bde_cesi_mtp?_t=ZN-8tezCXXQ3tO&_r=1" target="_blank"><i class="fa-brands fa-tiktok">
                  <img class="TikTok" 
                      src="./images/tiktok.png"></i></a>
          <a class="social-button instagram" href=" https://www.instagram.com/bde.cesi.montpellier?igsh=MWVhaWFvNGNvcDZuNw==" target="_blank"><i class="fa-brands fa-instagram">
          <img class="instagram" 
                      src="./images/instagram.png"></i></a>
      </div>
    </div>
  </div>

  <!-- Bas de page -->
  <div class="footer-bottom">
    <p>Copyright © 2025 - Tous droits réservés. <a href="./mentions_legales.html">Mentions légales</a></p>
  </div>
</footer>
<script src="../Controler/menu.js"></script> 
<script src="../Controler/voir_offre.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</html>
