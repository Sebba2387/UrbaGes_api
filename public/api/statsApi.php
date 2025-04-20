<?php
// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers CORS et JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Inclusion des dépendances
require_once __DIR__ . '/../../app/controllers/statsController.php';

// Récupération des données envoyées en JSON
$input = file_get_contents("php://input");
error_log("Données reçues : " . $input);  // Log des données brutes reçues

// Si les données sont manquantes ou invalides
if (!$input || !isset($input['action'])) {
    echo json_encode([
        "success" => false,
        "message" => "Requête invalide ou données manquantes"
    ]);
    exit;
}

// Décoder le JSON reçu
$data = json_decode($input, true);
error_log("Données décodées : " . print_r($data, true));  // Log des données décodées

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'getStats':
            require_once __DIR__ . '/../../app/controllers/gepController.php';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Action inconnue"]);
            exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
error_log(print_r($data, true));  // Log des données après traitement

?>
