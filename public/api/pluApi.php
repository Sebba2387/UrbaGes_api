<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Assure-toi d'inclure le bon contrôleur pour gérer les PLU !
require_once __DIR__ . '/../../app/controllers/pluController.php';

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Définition du fichier de log
define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Requête reçue : " . file_get_contents("php://input") . "\n", FILE_APPEND);

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// CORS (évite les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// 🔍 Récupération des données JSON envoyées par Fetch
$data = json_decode(file_get_contents("php://input"), true);

// Debugging : Affiche les données reçues
file_put_contents("log_api.txt", print_r($data, true), FILE_APPEND);

// Vérification de l'action
if (!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Aucune action reçue"]);
    exit;
}

// Exécution en fonction de l'action
switch ($data['action']) {
    case 'searchPlu':
        require_once __DIR__ . '/../../app/controllers/pluController.php';
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action inconnue : " . $data['action']]);
        exit;
}
