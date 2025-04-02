<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/dossierModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Définition du fichier de log
if (!defined('DEBUG_LOG')) {
    define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
}
file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Requête reçue : " . file_get_contents("php://input") . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérification de la connexion à la base de données
if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

$dossierModel = new DossierModel($pdo);

// Lire l'entrée JSON
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Requête invalide"]);
    exit;
}

// Vérifier si l'action est définie
$action = $input['action'] ?? null;
if (!$action) {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

switch ($action) {
    case 'searchDossier':
        $filters = [
            "nom_commune" => $input['nom_commune'] ?? '',
            "numero_dossier" => $input['numero_dossier'] ?? '',
            "id_cadastre" => $input['id_cadastre'] ?? '',
            "type_dossier" => $input['type_dossier'] ?? '',
            "sous_type_dossier" => $input['sous_type_dossier'] ?? '',
        ];

        $result = $dossierModel->searchDossier($filters);
        echo json_encode(["success" => true, "dossiers" => $result]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action non valide"]);
        break;
}
?>