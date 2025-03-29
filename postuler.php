<!doctype html> 
<html lang="fr"> 
   <head> 
      <meta charset="utf-8">
      <meta name="description" content="Postuler à une offre de stage">
      <title>Lebonplan</title>
      <link rel="stylesheet" href="../View/postuler.css">
      <link rel="icon" href="./images/logo_chap.png">
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
                    <li class="menu-item"><a href="./accueil_etu.HTML" class="top-level-entry ">Accueil</a></li>
                    <li class="menu-item"><a href="./contact_etu.HTML" class="top-level-entry">Contact</a></li>
                    <li class="menu-item"><a href="./profil.HTML" class="top-level-entry">Profil</a></li>
                    <li class="menu-item"><a href="./recherche_etu.HTML" class="top-level-entry active">Offres</a></li>
                </ul>

                <!-- Liens de Connexion et S'inscrire à droite -->
                <div class="auth-links">
                    <a href="./accueil.HTML" class="button">Déconnexion</a>
                </div>
            </nav>
        </div>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./offre.HTML">Offres</a></li>
                <li class="breadcrumb-item"><a href="./postuler.HTML">Postuler</a></li>
            </ol>
        </nav>
        <br>
     </header> 
   <body>

 <div class="container">
<div class="box" id="annonce-container">
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
 
        $id_annonce = isset($_GET['id_annonce']) ? (int)$_GET['id_annonce'] : 1;
 
        $query = "SELECT 
    a.Id_ann,
    a.contenu AS description,
    a.titre,
    e.nom_ent AS entreprise
FROM Annonce a
JOIN Entreprise e ON a.Id_ann = e.Id_ann
WHERE a.Id_ann = :id_annonce";
 
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_annonce' => $id_annonce]);
        $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if ($annonce) {
            echo '<h2 class="title">' . htmlspecialchars($annonce['titre']) . '</h2>';
            echo '<h5 class="title">Société | ' . htmlspecialchars($annonce['entreprise']) . '</h5>';
            echo '<h3>Présentation de l\'entreprise</h3>';
            echo '<p>' . nl2br(htmlspecialchars($annonce['description'])) . '</p>';
        } else {
            echo '<h2 class="title">Annonce introuvable.</h2>';
        }
        ?>
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
        <label for="cv">CV</label>
        <input type="file" id="cv" name="cv" style="display: none;" required>
        <label for="cv" class="btn-orange" style="color: white; font-weight: bold; display: inline-block; padding: 11px 20px; cursor: pointer;">AJOUTER MON CV</label>
        <p style="margin-top: 5px;">Poids max: 2Mo<br>Formats: .pdf, .doc, .docx, .odt, .rtf, .jpg ou .png</p>
      </div>

      <input type="hidden" name="id_ann" value="2">

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
    cible.scrollIntoView({
      behavior: "smooth"
    });
  }
}

</script>

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
        <li><a href="./coockies_etu.html">Cookies</a></li>
        <li><a href="./faq_etu.html">F.A.Q</a></li>
        <li><a href="./cgu_etu.html">Conditions générales</a></li>
        <li><a href="./protection_etu.html">Politique de protection des données</a></li>
        <li><a href="./mentions_legales_etu.html">Mentions légales</a></li>
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
<script src="../Controler/postuler.js"></script> 
  
</html>