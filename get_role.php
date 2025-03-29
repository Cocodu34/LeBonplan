<?php 
session_start();// Simuler une session de test (à désactiver en prod !)
$_SESSION['id'] = 999;
$_SESSION['email'] = "debug@fake.fr";
$_SESSION['prenom'] = "Debug";
$_SESSION['nom'] = "User";
$_SESSION['role'] = "pilote"; //Tu peux changer ici : etudiant, entreprise, etc. 
//echo "Session de débogage créée pour : " . $_SESSION['role'];


 
if (isset($_SESSION['role'])) {
    echo json_encode(['role' => $_SESSION['role']]);
} else {
    echo json_encode(['role' => null]);
}


