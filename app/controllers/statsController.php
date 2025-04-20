<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/statsModel.php';
require_once __DIR__ . '/../config/database.php';

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Vérification de la connexion à la base de données
if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

$statsModel = new StatsModel($pdo);
$data = json_decode(file_get_contents("php://input"), true);

// 🔹 Vérifier si $data contient bien les infos attendues
if (!$data) {
    echo json_encode(["success" => false, "message" => "Données JSON invalides"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'getStats':
            try {
                $stats = $statsModel->getStats();
                echo json_encode(["success" => true, "stats" => $stats]);
                exit;
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
                exit;
            }

        default:
            echo json_encode(["success" => false, "message" => "Action non définie"]);
            exit;
    }
}

// 🔹 Si aucune action valide n'a été détectée
echo json_encode(["success" => false, "message" => "Requête invalide"]);
exit;
