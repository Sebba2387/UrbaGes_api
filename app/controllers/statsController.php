<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../models/statsModel.php';
require_once __DIR__ . '/../config/database.php';

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Vérification de la connexion à la base de données
if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

// Instanciation du modèle StatsModel
$statsModel = new StatsModel($pdo);
// Lecture des données brutes JSON envoyées, puis décodage en tableau associatif PHP
$data = json_decode(file_get_contents("php://input"), true);

// Vérification si $data contient bien les infos attendues (JSON)
if (!$data) {
    echo json_encode(["success" => false, "message" => "Données JSON invalides"]);
    exit;
}

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        // ⚙️ Récupérer les statistiques
        case 'getStats':
            try {
                $stats = $statsModel->getStats();
                echo json_encode(["success" => true, "stats" => $stats]);
                exit;
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
                exit;
            }
        // Cas par défaut
        default:
            echo json_encode(["success" => false, "message" => "Action non définie"]);
            exit;
    }
}

// Si aucune action valide n'a été détectée
echo json_encode(["success" => false, "message" => "Requête invalide"]);
exit;

// Fichier de log pour debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
?>
