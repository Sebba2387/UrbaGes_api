<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification si les en-têtes HTTP ont déjà été envoyés avant l'exécution du script
if (headers_sent($file, $line)) {
    error_log("Les headers ont déjà été envoyés dans $file à la ligne $line");
    exit;
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../models/courrierModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Vérification de la connexion à la base de données
if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

// Lecture des données brutes JSON envoyées, puis décodage en tableau associatif PHP
$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

// Vérification que les données JSON ont été correctement décodées
if (!$input) {
    echo json_encode(["success" => false, "message" => "Requête invalide ou JSON mal formé"]);
    exit;
}

// Vérification que l'action a bien été spécifiée dans les données
$action = $input['action'] ?? null;
if (!$action) {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

// Instanciation du modèle CourrierModel
$courrierModel = new CourrierModel($pdo, $modificationCollection);

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
switch ($action) {
    // ➕ Ajouter un courrier
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
    // 🔍 Rechercher un courrier
    case 'searchCourrier':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $courrierModel->searchCourrier($data);
            echo json_encode($result);
        }
        break;
    // 🗑️ Supprimer un courrier
    case 'deleteCourrier':
        if (!isset($input['id_courrier'])) {
            echo json_encode(["success" => false, "message" => "ID du courrier manquant"]);
            exit;
        }
        $success = $courrierModel->deleteCourrier($input['id_courrier']);
        echo json_encode(['success' => $success, 'message' => $success ? "Courrier supprimé" : "Erreur lors de la suppression"]);
        break;
    // ✏️ Mettre à jour un courrier
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
    // 📌 Récupérer les données d'un courrier
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
    // ⚙️ Générer un courrier
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
    // Cas par défault
    default:
        echo json_encode(["success" => false, "message" => "Action non reconnue"]);
        break;
}

// Fichier de log pour debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
?>
