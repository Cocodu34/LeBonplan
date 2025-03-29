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
    $entreprise = htmlspecialchars($_POST['entreprise']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $surname = htmlspecialchars($_POST['surname']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars($_POST['address']);
    $SIRET = htmlspecialchars($_POST['SIRET']);
    $SIREN = htmlspecialchars($_POST['SIREN']);
    $domaine = htmlspecialchars($_POST['domaine']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash du mot de passe

    // Vérifier si l'email est déjà utilisé
    $stmt = $pdo->prepare("SELECT Id_uti FROM Utilisateur WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "Erreur : cet email est déjà utilisé.";
        exit;
    }

    try {
        // Insertion dans la table Utilisateur (compte entreprise)
        $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, prenom, email, mdp_crypte) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lastname, $surname, $email, $password]);

        $id_utilisateur = $pdo->lastInsertId();  // ID de l'utilisateur créé

        // Insertion dans la table Entreprise
        $stmt = $pdo->prepare("INSERT INTO Entreprise (Id_uti, nom_ent, adresse, SIREN, domaine_activite) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_utilisateur, $entreprise, $address, $SIREN, $domaine]);

        echo "Inscription réussie !";

    } catch (PDOException $e) {
        http_response_code(500);
        echo "Erreur lors de l'insertion : " . $e->getMessage();
        exit;
    }
}
?>
