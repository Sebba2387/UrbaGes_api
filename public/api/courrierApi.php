<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS (évite les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// Inclure le modèle et le contrôleur pour gérer les courriers
require_once __DIR__ . '/../../app/controllers/courrierController.php';

// 🔍 Récupération des données JSON envoyées par Fetch
$data = json_decode(file_get_contents("php://input"), true);

// Vérification de l'action
if (!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Aucune action reçue"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if (
        $data['action'] === 'addCourrier' ||
        $data['action'] === 'deleteCourrier' ||
        $data['action'] === 'searchCourrier' ||
        $data['action'] === 'updateCourrier' ||
        $data['action'] === 'getCourrierById'
    ) {
        require_once __DIR__ . '/../../app/controllers/courrierController.php';
    } else {
        echo json_encode(["success" => false, "message" => "Action inconnue"]);
        exit;
    }
}

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log(print_r($data, true));  // Affiche les données dans le fichier de log PHP
?>
