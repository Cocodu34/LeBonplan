<?php
header('Content-Type: text/html; charset=utf-8');
session_start(); // ← nécessaire même pour définir une session manuellement

$host = "localhost";
$dbname = "stage";
$username = "root";
$password = "root";

// ⚠️ Pour test : on fixe l'ID de l'étudiant connecté
$_SESSION['Id_etu'] = 1;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération de l'ID de l'étudiant connecté
if (!isset($_SESSION['Id_etu'])) {
    die("Erreur : utilisateur non connecté.");
}

$Id_etu = $_SESSION['Id_etu'];

// ⚠️ Si requête AJAX de recherche dans la wishlist
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);

    $query = "SELECT 
        a.Id_ann,
        a.titre,
        a.contenu AS description,
        e.nom_ent
    FROM Wishlist w
    JOIN Annonce a ON w.Id_ann = a.Id_ann
    JOIN Entreprise e ON e.Id_ann = a.Id_ann
    WHERE w.Id_etu = :id_etu";

    $params = ['id_etu' => $Id_etu]; // ✅ paramètre bien nommé

    if (!empty($search)) {
        $query .= " AND (
            a.titre LIKE :search 
            OR a.contenu LIKE :search 
            OR e.nom_ent LIKE :search
        )";
        $params['search'] = '%' . $search . '%'; // ✅ ajout si non vide
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($annonces) > 0) {
        foreach ($annonces as $annonce) {
            echo '<div class="annonce">';
            echo '  <div class="box">';
            echo '    <h2 class="annonce-title">' . htmlspecialchars($annonce['titre']) . '</h2>';
            echo '    <p class="annonce-description">Société: ' . htmlspecialchars($annonce['nom_ent']) . '</p>';
            echo '    <button class="voir-offre-btn">Voir l\'offre</button>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucune offre trouvée.</p>';
    }
    exit;
}

// Chargement du profil
$stmtProfil = $pdo->prepare("SELECT nom, prenom, email, descriptif FROM Utilisateur WHERE Id_uti = :Id");
$stmtProfil->execute(['Id' => $Id_etu]);
$profil = $stmtProfil->fetch(PDO::FETCH_ASSOC);
?>



<!doctype html> 
<html lang="fr"> 
<head> 
  <meta charset="utf-8">
  <meta name="description" content="Postuler à une offre de stage">
  <title>Lebonplan</title>
  <link rel="stylesheet" href="profil_etu.css">
  <link rel="icon" href="logo_chap.png">
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
    <img src="logo.png" alt="Logo" style="width: 500px;"> 
</header>

<header>
  <div class="navbar">
    <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu">&#9776;</button>
    <nav class="nav-items-container">
      <ul class="main-menu" id="main-menu">
        <li class="menu-item"><a href="accueil_etu.php" class="top-level-entry ">Accueil</a></li>
        <li class="menu-item"><a href="contact_etu.HTML" class="top-level-entry">Contact</a></li>
        <li class="menu-item"><a href="profil.HTML" class="top-level-entry active">Profil</a></li>
        <li class="menu-item"><a href="recherche_etu.php" class="top-level-entry">Offre</a></li>
      </ul>
      <div class="auth-links">
        <a href="index.php" class="button">Déconnexion</a>
      </div>
    </nav>
  </div>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="./profil.HTML">Profil</a></li>
    </ol>
  </nav>
  <br>
</header>

<main class="content">
  <section class="company">
    <img src="anakin.png" class="company-logo">
    <div class="company-info">
      <h2><?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></h2>
      <p><?= htmlspecialchars($profil['email']) ?></p>
      <p><?= nl2br(htmlspecialchars($profil['descriptif'])) ?></p>
      <a href="modifierprofil_etu.html">
        <button class="profile">Modifier mon profil</button>
      </a>
    </div>
  </section>
</main>

<!-- Barre de recherche -->
<div class="search-container" style="text-align:center; margin:20px;">
  <input type="text" id="search-input" class="search-input" placeholder="Rechercher un stage (par description, mode ou entreprise)">
  <button type="button" class="search-button" onclick="rechercherOffres()">Rechercher</button>
</div>

<h1 class="titre-page">Vos stages en wishlist</h1>

<!-- Conteneur des annonces -->
<div class="container" id="annonces-container" style="background-color: #e6f2ff">
  <!-- Les résultats AJAX apparaîtront ici -->
</div>

<script>
  function rechercherOffres() {
      const input = document.getElementById("search-input").value.trim();
      const xhr = new XMLHttpRequest();
      xhr.open("GET", window.location.pathname + "?search=" + encodeURIComponent(input), true);
      xhr.onreadystatechange = function () {
          if (xhr.readyState === 4 && xhr.status === 200) {
              document.getElementById("annonces-container").innerHTML = xhr.responseText;
          }
      };
      xhr.send();
  }

  // Rechercher dès la frappe
  document.getElementById("search-input").addEventListener("keyup", function () {
      rechercherOffres();
  });

  // Charger la wishlist au début
  window.onload = function () {
      rechercherOffres();
  };
</script>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-column">
      <img src="logo_chap.png" alt="Logo principal" class="footer-logo">
    </div>
    <div class="footer-column">
      <h3>Coordonnées</h3>
      <a style='color:#ffffff' href="https://www.google.fr/maps/place/Campus+CESI/">Immeuble Le Quatrième Zone Aéroportuaire, 34130 Mauguio</a>
      <p><i class="fa-solid fa-envelope"></i> contact@cesi.fr</p>
      <p><i class="fa-solid fa-phone"></i> +33 6 12 34 56 78</p>
    </div>
    <div class="footer-column">
      <h3>Navigation</h3>
      <ul class="footer-links">
        <li><a href="./coockies_etu.html">Cookies</a></li>
        <li><a href="./faq_etu.html">F.A.Q</a></li>
        <li><a href="./cgu_etu.html">Conditions générales</a></li>
        <li><a href="./protection_etu.html">Protection des données</a></li>
        <li><a href="./mentions_legales_etu.html">Mentions légales</a></li>
      </ul>
    </div>
    <div class="footer-column">
      <h3>Suivez-nous</h3>
      <div class="social-buttons">
        <a href="https://x.com/cesi_officiel?s=21" target="_blank"><img src="./images/Twitter.png"></a>
        <a href="https://www.tiktok.com/@bde_cesi_mtp" target="_blank"><img src="./images/tiktok.png"></a>
        <a href="https://www.instagram.com/bde.cesi.montpellier" target="_blank"><img src="./images/instagram.png"></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 - Tous droits réservés. <a href="./mentions_legales.html">Mentions légales</a></p>
  </div>
</footer>
<script src="menu.js"></script> 
</body>
</html>

