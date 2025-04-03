<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ⚠ Vérifie si les headers sont déjà envoyés
if (headers_sent($file, $line)) {
    error_log("Les headers ont déjà été envoyés dans $file à la ligne $line");
    exit;
}

require_once __DIR__ . '/../models/dossierModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

// CORS (éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
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

// Lire l'entrée JSON en vérifiant si elle est vide
$rawInput = file_get_contents("php://input");
if (!$rawInput) {
    echo json_encode(["success" => false, "message" => "Aucune donnée reçue"]);
    exit;
}

// Décoder l'entrée JSON
$input = json_decode($rawInput, true);

// Vérifier si le JSON est bien formé
if (!$input) {
    echo json_encode(["success" => false, "message" => "Requête invalide (JSON mal formé)"]);
    exit;
}

// Log des données reçues pour déboguer
error_log("Données reçues : " . print_r($input, true));

// Vérifier si l'action est définie dans les données reçues
$action = $input['action'] ?? null;
if (!$action) {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

$dossierModel = new DossierModel($pdo);

// Traiter l'action en fonction du type demandé
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

    case 'getDossierById':
        // Vérifier que 'id_dossier' est bien fourni dans l'input
        if (!isset($input['id_dossier'])) {
            echo json_encode(["success" => false, "message" => "ID dossier manquant"]);
            exit;
        }

        // Récupérer l'ID du dossier et le traiter
        $id_dossier = $input['id_dossier'];

        // Appeler la méthode pour obtenir le dossier par ID
        $result = $dossierModel->getDossierById($id_dossier);
        
        // Vérifier si un dossier est trouvé ou non
        if ($result) {
            echo json_encode(["success" => true, "dossier" => $result]);
        } else {
            echo json_encode(["success" => false, "message" => "Dossier non trouvé"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action non valide"]);
        break;
}

?>
