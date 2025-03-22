<?php
require_once __DIR__ . '/../../app/controllers/userController.php';

error_reporting(E_ALL);
ini_set('display_errors', 1); // Affiche les erreurs directement

// Vérification du jeton CSRF (facultatif, mais recommandé pour plus de sécurité)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer your_token_here') {
        echo json_encode(["success" => false, "message" => "Accès non autorisé"]);
        exit;
    }
}

$data = json_decode(file_get_contents("php://input"), true);

// Debug : Affiche ce que reçoit le serveur
var_dump($data);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'login') {
        // Appel du contrôleur pour vérifier les identifiants
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide"]);
}
?>
