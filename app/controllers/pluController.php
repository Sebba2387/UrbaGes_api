<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification si les headers sont déjà envoyés
if (headers_sent($file, $line)) {
    error_log("Les headers ont déjà été envoyés dans $file à la ligne $line");
    exit;
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../models/pluModel.php';
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

// Vérification si $input contient bien les infos attendues (JSON)
$rawInput = file_get_contents("php://input");
if (!$rawInput) {
    echo json_encode(["success" => false, "message" => "Aucune donnée reçue"]);
    exit;
}

$input = json_decode($rawInput, true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Requête invalide (JSON mal formé)"]);
    exit;
}

// Récupération de l'action
$action = $input['action'] ?? null;
if (!$action) {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

// Instanciation du modèle PluModel
$pluModel = new PluModel($pdo, $modificationCollection);

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
switch ($action) {
    // 🔍 Rechercher un PLU
    case 'searchPlu':
        $id_commune = $input['id_commune'] ?? '';
        $statut_zonage = $input['statut_zonage'] ?? '';
        $statut_pres = $input['statut_pres'] ?? '';
        $etat_plu = $input['etat_plu'] ?? '';
        
        $result = $pluModel->searchPlu($id_commune, $statut_zonage, $statut_pres, $etat_plu);
        echo json_encode(["success" => true, "plu" => $result]);
        break;
    // 📌 Récupérer les données d'un PLU
    case 'getPluById':
        if (!isset($input['id_plu'])) {
            echo json_encode(["success" => false, "message" => "ID PLU manquant"]);
            exit;
        }
        $plu = $pluModel->getPluById($input['id_plu']);
        echo json_encode(["success" => true, "plu" => $plu]);
        break;
    // ✏️ Mettre à jour des données d'un PLU
    case 'updatePlu':
        $requiredFields = ['id_plu', 'type_plu', 'etat_plu', 'date_plu', 'systeme_ass', 'statut_zonage', 'statut_pres', 'date_annexion', 'lien_zonage', 'lien_dhua', 'observation_plu'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                echo json_encode(["success" => false, "message" => "Champ manquant : $field"]);
                exit;
            }
        }
        
        $success = $pluModel->updatePlu($input);
        echo json_encode(["success" => $success, "message" => $success ? "PLU mis à jour" : "Échec de la mise à jour"]);
        break;
    // 📌 Récupérer les noms de toutes les communes
    case 'getCommunes':
        $communes = $pluModel->getAllCommunes();
        if ($communes) {
            echo json_encode(["success" => true, "communes" => $communes]);
        } else {
            echo json_encode(["success" => false, "message" => "Aucune commune trouvée"]);
        }
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
// error_log("Données reçues : " . print_r($input, true));
?>
