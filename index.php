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
?>
<!doctype html> 
<html lang="fr"> 
   <head> 
      <meta charset="utf-8">
      <meta name="description" content="Postuler à une offre de stage">
      <title>Lebonplan</title>
      <link rel="stylesheet" href="accueil.css">
      <link rel="icon" href="logo_chap.png">
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script src="deconnexion.js"></script>
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
                    <li class="menu-item"><a href="index.php" class="top-level-entry active">Accueil</a></li>
                    <li class="menu-item"><a href="contact.html" class="top-level-entry">Contact</a></li>
                </ul>
                <div class="auth-links">
                    <a href="connexion.html" class="navitem">Connexion</a>
                    <a href="choix_compte_insc.html" class="button">S'inscrire</a>
                </div>
            </nav>
        </div>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            </ol>
        </nav>
        <br>
    </header>

    <main>
        <!-- Annonces les plus visitées dynamiques -->
        <section class="job-listings" id="annonces_visitees">
            <h2>Offres les plus visitées</h2>
            <div class="jobs-grid">
                <?php if (!empty($annonces_visitees)): ?>
                    <?php foreach ($annonces_visitees as $annonce): ?>
                        <article class="job-card">
                            <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                            <form action="connexion.html" method="get">
                                <button type="submit">Voir l'offre</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune annonce populaire pour le moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <p>Vous recherchez un Stage ou une Alternance ?</p>
                <h1>LeBonPlan est là pour vous aider !</h1>
                <p>Avec plus de 15 millions d’élèves inscrits et 45 000 entreprises référencées, vous trouverez forcément une annonce qui vous correspond.</p>
                <button><a style="color: #2368e1; text-decoration: none" href="./choix_compte_insc.html">Inscrivez-vous !</a></button>
            </div>
        </section>
    </main>
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
      <a style='color:#ffffff' href="https://www.google.fr/maps/place/Campus+CESI/@43.5792319,3.9432547,794m/data=!3m2!1e3!4b1!4m6!3m5!1s0x12b6afdaa52cccbf:0xa4dd1993e0746bd!8m2!3d43.5792281!4d3.9481256!16s%2Fg%2F1v202y6s?entry=ttu">
        Immeuble Le Quatrième Zone Aéroportuaire de Montpellier Méditerranée, 34130 Mauguio</a>
      <p><i class="fa-solid fa-envelope"></i> contact@cesi.fr</p>
      <p><i class="fa-solid fa-phone"></i> +33 6 12 34 56 78</p>
    </div>

    <!-- Colonne 3 : Navigation -->
    <div class="footer-column">
      <h3>Navigation</h3>
      <ul class="footer-links">
        <li><a href="coockies.html">Cookies</a></li>
        <li><a href="faq.html">F.A.Q</a></li>
        <li><a href="cgu.html">Conditions générales</a></li>
        <li><a href="protection.html">Politique de protection des données</a></li>
        <li><a href="mentions_legales.html">Mentions légales</a></li>
      </ul>
    </div>

    <!-- Colonne 4 : Réseaux sociaux -->
    <div class="footer-column">
      <h3>Suivez-nous</h3>
      <div class="social-buttons">
        <a class="social-button twitter" href="https://x.com/cesi_officiel?s=21" target="_blank"><img class="twitter" src="Twitter.png"></a>
        <a class="social-button tiktok" href="https://www.tiktok.com/@bde_cesi_mtp?_t=ZN-8tezCXXQ3tO&_r=1" target="_blank"><img class="TikTok" src="tiktok.png"></a>
        <a class="social-button instagram" href="https://www.instagram.com/bde.cesi.montpellier?igsh=MWVhaWFvNGNvcDZuNw==" target="_blank"><img class="instagram" src="instagram.png"></a>
      </div>
    </div>
  </div>

  <!-- Bas de page -->
  <div class="footer-bottom">
    <p>Copyright © 2025 - Tous droits réservés. <a href="./mentions_legales.html">Mentions légales</a></p>
  </div>
</footer>
<script src="menu.js"></script> 
</html>
