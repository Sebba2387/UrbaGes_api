<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../../app/controllers/communeController.php';

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'searchCommune') {
        require_once __DIR__ . '/../../app/controllers/communeController.php';
    } elseif ($data['action'] === 'addCommune') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'updateCommune') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'getCommune') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'getCommunes') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'deleteCommune') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
}

// Fichier de log pour debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
// file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Requête reçue : " . file_get_contents("php://input") . "\n", FILE_APPEND);
?>
