<?php
header('Content-Type: text/html; charset=utf-8');

// Connexion à la BDD
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

// Récupération de l'ID de l'annonce passé en GET
$id_ann = isset($_GET['id_ann']) ? (int)$_GET['id_ann'] : 0;

// Requête pour récupérer l’annonce et son entreprise
$query = "SELECT 
    a.Id_ann,
    a.titre,
    a.contenu AS description,
    e.nom_ent AS entreprise
FROM Annonce a
JOIN Entreprise e ON a.Id_ann = e.Id_ann
WHERE a.Id_ann = :id_ann";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_ann' => $id_ann]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html> 
<html lang="fr"> 
<head> 
  <meta charset="utf-8">
  <meta name="description" content="Postuler à une offre de stage">
  <title>Lebonplan</title>
  <link rel="stylesheet" href="postuler.css">
  <link rel="icon" href="logo_chap.png">
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
          <li class="menu-item"><a href="accueil_etu.php" class="top-level-entry">Accueil</a></li>
          <li class="menu-item"><a href="contact_etu.HTML" class="top-level-entry">Contact</a></li>
          <li class="menu-item"><a href="profil.HTML" class="top-level-entry">Profil</a></li>
          <li class="menu-item"><a href="recherche_etu.php" class="top-level-entry active">Offres</a></li>
        </ul>
        <div class="auth-links">
          <a href="index.php" class="button">Déconnexion</a>
        </div>
      </nav>
    </div>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="recherche_etu.php">Offres</a></li>
        <li class="breadcrumb-item"><a href="postuler.php?id_ann=<?= $id_ann ?>">Postuler</a></li>
      </ol>
    </nav>
    <br>
  </header>

  <div class="container">
    <div class="box" id="annonce-container">
      <?php if ($annonce): ?>
        <h2 class="title"><?= htmlspecialchars($annonce['titre']) ?></h2>
        <h5 class="title">Société | <?= htmlspecialchars($annonce['entreprise']) ?></h5>
        <h3>Présentation de l'entreprise</h3>
        <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
      <?php else: ?>
        <h2 class="title">Annonce introuvable.</h2>
      <?php endif; ?>
    </div>
  </div>

  <div class="container2" id="formulaire-candidature">
    <div class="box2">
      <div class="bottom-content">
        <form action="traitement_postuler.php" method="POST" enctype="multipart/form-data">
          <div class="box3">
            <label for="sexe">Sexe :</label>
            <select id="sexe" name="sexe">
              <option value="homme">Homme</option>
              <option value="femme">Femme</option>
              <option value="autre">Autre</option>
              <option value="non-dit">Préférer ne pas le dire</option>
            </select>
          </div>

          <div>
            <label>Nom :</label>
            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
          </div>

          <div>
            <label>Prénom :</label>
            <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required>
          </div>

          <div>
            <label>Courriel :</label>
            <input type="email" id="couriel" name="couriel" placeholder="courriel@email.fr" required>
          </div>

          <div class="form-group1">
            <label for="message">VOTRE MESSAGE AU RECRUTEUR</label>
            <textarea id="message" name="message" maxlength="800"></textarea>
          </div>

          <div class="form-group">
            <br><br>
                        <input type="file" name="file" id="file" accept=".pdf">
                        <button type="button" id="upload-btn">Téléverser</button>
                        <div id="upload-message"></div>
                        <br><br>
                        <div class="small">Poids max. 2Mo</div>
                        <div class="small">Formats .pdf</div>
                        <br><br>
          </div>

          <!-- ID de l’annonce passé au traitement -->
          <input type="hidden" name="id_ann" value="<?= htmlspecialchars($id_ann) ?>">

          <div class="buttons">
            <button type="reset" class="cancel">Annuler</button>
            <button type="submit" class="submit">Envoyer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function scrollToBottom() {
      const cible = document.getElementById("formulaire-candidature");
      if (cible) {
        cible.scrollIntoView({ behavior: "smooth" });
      }
    }
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
          <li><a href="coockies_etu.html">Cookies</a></li>
          <li><a href="faq_etu.html">F.A.Q</a></li>
          <li><a href="cgu_etu.html">Conditions générales</a></li>
          <li><a href="protection_etu.html">Protection des données</a></li>
          <li><a href="mentions_legales_etu.html">Mentions légales</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Suivez-nous</h3>
        <div class="social-buttons">
          <a href="https://x.com/cesi_officiel" target="_blank"><img src="Twitter.png"></a>
          <a href="https://www.tiktok.com/@bde_cesi_mtp" target="_blank"><img src="tiktok.png"></a>
          <a href="https://www.instagram.com/bde.cesi.montpellier" target="_blank"><img src="instagram.png"></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 - Tous droits réservés. <a href="mentions_legales.html">Mentions légales</a></p>
    </div>
  </footer>

  <script src="postuler.js"></script>
  <script src="inscription_etu.js"></script> 
</body>
</html>

