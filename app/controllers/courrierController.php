<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (headers_sent($file, $line)) {
    error_log("Les headers ont déjà été envoyés dans $file à la ligne $line");
    exit;
}

require_once __DIR__ . '/../models/courrierModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Requête invalide ou JSON mal formé"]);
    exit;
}

$action = $input['action'] ?? null;
if (!$action) {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

$courrierModel = new CourrierModel($pdo, $modificationCollection);

switch ($action) {

    case 'addCourrier':
        if (!isset($input['type_courrier']) || !isset($input['libelle_courrier']) || !isset($input['corps_courrier'])) {
            echo json_encode(["success" => false, "message" => "Données manquantes pour l'ajout du courrier"]);
            exit;
        }

        $data = [
            'type_courrier' => $input['type_courrier'],
            'libelle_courrier' => $input['libelle_courrier'],
            'corps_courrier' => $input['corps_courrier']
        ];

        $success = $courrierModel->addCourrier($data);
        echo json_encode(['success' => $success, 'message' => $success ? "Courrier ajouté" : "Erreur lors de l'ajout"]);
        break;
    
    case 'searchCourrier':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $courrierModel->searchCourrier($data);
            echo json_encode($result);
        }
        break;

    case 'deleteCourrier':
        if (!isset($input['id_courrier'])) {
            echo json_encode(["success" => false, "message" => "ID du courrier manquant"]);
            exit;
        }

        $success = $courrierModel->deleteCourrier($input['id_courrier']);
        echo json_encode(['success' => $success, 'message' => $success ? "Courrier supprimé" : "Erreur lors de la suppression"]);
        break;

    case 'updateCourrier':
        if (!isset($input['id_courrier']) || !isset($input['type_courrier']) || !isset($input['libelle_courrier']) || !isset($input['corps_courrier'])) {
            echo json_encode(["success" => false, "message" => "Données incomplètes pour la mise à jour"]);
            exit;
        }

        $data = [
            'id_courrier' => $input['id_courrier'],
            'type_courrier' => $input['type_courrier'],
            'libelle_courrier' => $input['libelle_courrier'],
            'corps_courrier' => $input['corps_courrier']
        ];

        $success = $courrierModel->updateCourrier($data);
        echo json_encode(['success' => $success, 'message' => $success ? "Courrier mis à jour" : "Erreur lors de la mise à jour"]);
        break;

    case 'getCourrierById':
        if (!isset($input['id_courrier'])) {
            echo json_encode(["success" => false, "message" => "ID du courrier manquant"]);
            exit;
        }

        $courrier = $courrierModel->getCourrierById($input['id_courrier']);

        if ($courrier) {
            echo json_encode(['success' => true, 'courrier' => $courrier]);
        } else {
            echo json_encode(['success' => false, 'message' => "Courrier introuvable"]);
        }
        break;

    case 'genererCourrier':
        $id_courrier = $input['id_courrier'] ?? null;
        $id_dossier = $input['id_dossier'] ?? null;
    
        if (!$id_courrier || !$id_dossier) {
            echo json_encode(['success' => false, 'message' => 'id_courrier ou id_dossier manquant']);
            exit;
        }
    
        $result = $courrierModel->getDonneesCourrierAvecDossier($id_courrier, $id_dossier);
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Données introuvables']);
            exit;
        }
    
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action non reconnue"]);
        break;
}
?>
