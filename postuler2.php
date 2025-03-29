<?php
session_start();
header('Content-Type: application/json');

// Si pas de session, erreur
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo json_encode(['error' => 'non_connecte']);
    exit();
}

// Si c'est un pilote → retour vide
if ($_SESSION['role'] === 'pilote') {
    echo json_encode([]); // aucune donnée injectée
    exit();
}

// Sinon (étudiant)
$response = array(
    'nom' => isset($_SESSION['nom']) ? $_SESSION['nom'] : '',
    'prenom' => isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '',
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
    'civilite' => isset($_SESSION['civilite']) ? $_SESSION['civilite'] : ''
);

echo json_encode($response);
