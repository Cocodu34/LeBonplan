<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'stage';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupération des champs du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $mot_de_passe = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $majorite = isset($_POST['majorite']) ? (int)$_POST['majorite'] : 0;
    $permis = isset($_POST['permis']) ? 1 : 0;
    $civilite = htmlspecialchars($_POST['civilite']);

    // Gestion du fichier CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cvContent = file_get_contents($_FILES['cv']['tmp_name']);
    } else {
        echo "Erreur lors du téléchargement du CV.";
        exit;
    }

    try {
        // Vérifier si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT Id_uti FROM Utilisateur WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo "Erreur : cet email est déjà utilisé.";
            exit;
        }

        // Insertion dans la table Utilisateur
        $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, prenom, email, mdp_crypte) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $mot_de_passe]);

        $id_utilisateur = $pdo->lastInsertId();

        // Insertion dans la table Etudiant
        $stmt = $pdo->prepare("INSERT INTO Etudiant (Id_etu, cv, Majorite, permis, Civilite, Id_uti) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_utilisateur, $cvContent, $majorite, $permis, $civilite, $id_utilisateur]);

        echo "Inscription réussie !";

    } catch (PDOException $e) {
        http_response_code(500);
        echo "Erreur lors de l'insertion : " . $e->getMessage();
        exit;
    }
}
?>