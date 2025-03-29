 <?php
session_start();

$host = 'localhost';
$dbname = 'stage';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {

        $email = $_POST["email"];
        $password = $_POST["password"];

        $sql = "SELECT id_uti, nom, prenom, role, mdp_crypte FROM utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($password, $utilisateur['mdp_crypte'])) {
            // On stocke les infos utiles en session
            $_SESSION['id'] = $utilisateur['id_uti'];
            $_SESSION['email'] = $email;
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['prenom'] = $utilisateur['prenom'];
            $_SESSION['role'] = $utilisateur['role'];

// Redirection selon le rôle
            switch ($utilisateur['role']) {
                case 'etudiant':
                    header("Location: accueil_etu.php");
                    break;
                case 'entreprise':
                    header("Location: accueil_ent.php");
                    break;
                case 'pilote':
                    header("Location: accueil_etu.php");
                    break;
                default:
                    echo "Rôle inconnu.";
            }

            exit();
        } else {
            echo "Échec de la connexion : email ou mot de passe incorrect.";
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
}
?>

