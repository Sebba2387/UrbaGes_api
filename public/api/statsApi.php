<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../../app/controllers/statsController.php';

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Lecture des données brutes JSON envoyées, puis décodage en tableau associatif PHP
$input = file_get_contents("php://input");
// error_log("Données reçues : " . $input);  // Log des données brutes reçues


// Vérification de l'action
if (!$input || !isset($input['action'])) {
    echo json_encode([
        "success" => false,
        "message" => "Requête invalide ou données manquantes"
    ]);
    exit;
}

// Décoder le JSON reçu
$data = json_decode($input, true);
// error_log("Données décodées : " . print_r($data, true));  // Log des données décodées

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
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

// Fichier de log pour debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
// error_log(print_r($data, true));  // Log des données après traitement
?>
