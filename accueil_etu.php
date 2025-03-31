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

// Annonces les plus visitées
$annonces_visitees = [];

try {
    $query = "SELECT a.Id_ann, a.titre, COUNT(*) AS nb_visites
              FROM Visiter v
              JOIN Annonce a ON v.Id_ann = a.Id_ann
              GROUP BY v.Id_ann
              ORDER BY nb_visites DESC
              LIMIT 4";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $annonces_visitees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Requête AJAX de recherche
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
                OR ent.domaine_activite LIKE :search";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($annonces) > 0) {
        foreach ($annonces as $annonce) {
            echo '<div class="annonce">';
            echo '  <div class="box">';
            echo '    <h2 class="annonce-title">' . htmlspecialchars($annonce['nom']) .' '.  htmlspecialchars($annonce['prenom']) .'</h2>';
            if (!empty($annonce['etablissement'])) {
                echo '<h3 class="annonce-title"> Établissement : ' . htmlspecialchars($annonce['etablissement']) . '</h3>';
            } elseif (!empty($annonce['entreprise'])) {
                echo '<h3 class="annonce-title"> Société : ' .  htmlspecialchars($annonce['entreprise']) . '</h3>';
            }
            echo '    <p class="annonce-mode">' . htmlspecialchars($annonce['descriptif']) . '</p>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucune offre trouvée.</p>';
    }

    exit;
}
?>

<!doctype html> 
<html lang="fr"> 
<head> 
    <meta charset="utf-8">
    <meta name="description" content="Postuler à une offre de stage">
    <title>Lebonplan</title>
    <link rel="stylesheet" href="accueil.css">
    <link rel="icon" href="logo_chap.png">
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 20px;
            margin: 0 auto;
            gap: 20px;
            background-color: white;
        }
        .box {
            width: 80vw;
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
    <img src="logo.png" alt="Logo" style="width: 500px;"> 
</header>

<header>
    <div class="navbar">
        <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu">&#9776;</button>
        <nav class="nav-items-container">
            <ul class="main-menu" id="main-menu">
                <li class="menu-item"><a href="accueil_etu.php" class="top-level-entry active">Accueil</a></li>
                <li class="menu-item"><a href="contact_etu.HTML" class="top-level-entry">Contact</a></li>
                <li class="menu-item"><a href="profil.php" class="top-level-entry">Profil</a></li>
                <li class="menu-item"><a href="recherche_etu.php" class="top-level-entry">Offre</a></li>
            </ul>
            <div class="auth-links">
                <a href="index.php" class="button">Déconnexion</a>
            </div>
        </nav>
    </div>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="accueil_etu.php">Accueil</a></li>
        </ol>
    </nav>
    <br>
</header>

<main style="background-color: #f9f9f9;">
    <br><br>
    <div class="search-container" style="text-align:center; margin:20px; background-color: #f9f9f9;">
        <input type="text" id="search-input" class="search-input" placeholder="Rechercher un nom ou prénom de profil">
        <button type="button" class="search-button" onclick="rechercherOffres()">Rechercher</button>
    </div>

    <div class="container" id="annonces-container" style="background-color: #f9f9f9;">
        <!-- Résultats AJAX -->
    </div>

    <!-- Annonces les plus visitées dynamiques -->
    <section class="job-listings" id="annonces_visitees">
        <h2>Offres les plus visitées</h2>
        <div class="jobs-grid">
            <?php if (!empty($annonces_visitees)): ?>
                <?php foreach ($annonces_visitees as $annonce): ?>
                    <article class="job-card">
                        <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                        <form action="postuler.php" method="get">
                            <input type="hidden" name="id_ann" value="<?= $annonce['Id_ann'] ?>">
                            <button type="submit" class="verifier-btn">Voir l'offre</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune annonce populaire pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="hero">
        <div class="hero-content">
            <p>Vous recherchez un Stage ou une Alternance ?</p>
            <h1>LeBonPlan est là pour vous aider !</h1>
            <p>Avec plus de 15 millions d’élèves inscrits et 45 000 entreprises référencées, vous trouverez forcément une annonce qui vous correspond.</p>
        </div>
    </section>
</main>

<script>
function rechercherOffres() {
    const input = document.getElementById("search-input").value.trim();

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

document.getElementById("search-input").addEventListener("keyup", rechercherOffres);
</script>
    </body> 
    <footer class="footer">
      <div class="footer-container">
        <!-- Colonne 1 : Logos -->
        <div class="footer-column">
          <img src="logo_chap.png" alt="Logo principal" class="footer-logo">
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
            <li><a href="coockies_etu.html">Cookies</a></li>
            <li><a href="faq_etu.html">F.A.Q</a></li>
            <li><a href="cgu_etu.html">Conditions générales</a></li>
            <li><a href="protection_etu.html">Politique de protection des données</a></li>
            <li><a href="mentions_legales_etu.html">Mentions légales</a></li>
          </ul>
        </div>
    
        <!-- Colonne 4 : Réseaux sociaux -->
        <div class="footer-column">
          <h3>Suivez-nous</h3>
          <div class="social-buttons">
            <a class="social-button twitter" href="https://x.com/cesi_officiel?s=21" target="_blank"><i class="fa-brands fa-twitter">
              <img class="twitter" 
                          src="Twitter.png"></i></a>
              <a class="social-button tiktok" href=" https://www.tiktok.com/@bde_cesi_mtp?_t=ZN-8tezCXXQ3tO&_r=1" target="_blank"><i class="fa-brands fa-tiktok">
                      <img class="TikTok" 
                          src="tiktok.png"></i></a>
              <a class="social-button instagram" href=" https://www.instagram.com/bde.cesi.montpellier?igsh=MWVhaWFvNGNvcDZuNw==" target="_blank"><i class="fa-brands fa-instagram">
              <img class="instagram" 
                          src="instagram.png"></i></a>
          </div>
        </div>
      </div>
    
      <!-- Bas de page -->
      <div class="footer-bottom">
        <p>Copyright © 2025 - Tous droits réservés. <a href="./mentions_legales.html">Mentions légales</a></p>
      </div>
    </footer>
    <script src="menu.js"></script> 
    <script src="voir_offre.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </html>
