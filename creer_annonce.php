<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'entreprise') {
    http_response_code(403);
    echo "Accès refusé.";
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stage;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

$data = json_decode(file_get_contents("php://input"), true);
$titre = isset($data['titre']) ? $data['titre'] : '';
$contenu = isset($data['contenu']) ? $data['contenu'] : '';

if (empty($titre) || empty($contenu)) {
    http_response_code(400);
    echo "Tous les champs sont obligatoires.";
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO annonce (titre, contenu, id_ent) VALUES (:titre, :contenu, :id_ent)");
    $stmt->execute([
        ":titre" => $titre,
        ":contenu" => $contenu,
        ":id_ent" => $_SESSION['id']
    ]);
    echo "Annonce publiée avec succès !";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur lors de la publication : " . $e->getMessage();
}
