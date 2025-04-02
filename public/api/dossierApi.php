<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le modèle et le contrôleur pour gérer les dossiers
require_once __DIR__ . '/../../app/controllers/dossierController.php';

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

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

// Vérification de l'action
if (!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Aucune action reçue"]);
    exit;
}

// Vérification de la méthode HTTP et de l'action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'searchDossier':
            require_once __DIR__ . '/../../app/controllers/dossierController.php';
            break;

        default:
            echo json_encode(["success" => false, "message" => "Action inconnue"]);
            exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Méthode non supportée ou action manquante"]);
}
?>
