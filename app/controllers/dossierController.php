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

$dossierModel = new DossierModel($pdo, $modificationCollection);

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
        if (!isset($input['id_dossier'])) {
            echo json_encode(["success" => false, "message" => "ID dossier manquant"]);
            exit;
        }
    
        $id_dossier = $input['id_dossier'];
        $result = $dossierModel->getDossierById($id_dossier);
    
        if ($result && isset($result['dossier'])) {
            echo json_encode([
                "success" => true,
                "dossier" => $result['dossier'],
                "utilisateurs" => $result['utilisateurs']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Dossier non trouvé"]);
            exit;
        }
        break;

    case 'updateDossier':
        $success = $dossierModel->updateDossier([
            'id_dossier' => $input['id_dossier'],
            'numero_dossier' => $input['numero_dossier'],
            'id_cadastre' => $input['id_cadastre'],
            'libelle' => $input['libelle'],
            'date_demande' => $input['date_demande'],
            'date_limite' => $input['date_limite'],
            'statut' => $input['statut'],
            'lien_calypso' => $input['lien_calypso'],
            'type_dossier' => $input['type_dossier'],
            'sous_type_dossier' => $input['sous_type_dossier'],
            'pseudo' => $input['pseudo'] // ajouté ici
        ]);
        echo json_encode(['success' => $success]);
        break;

    case 'getCommunes':
        // Vérifie que la connexion à la base de données fonctionne
        $communes = $dossierModel->getAllCommunes();  // Appel à la méthode pour récupérer toutes les communes
        
        if ($communes) {
            echo json_encode(['success' => true, 'communes' => $communes]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune commune trouvée']);
        }
        break;

    case 'addDossier':
        // Vérifie que les données nécessaires sont présentes
        if (!isset($input['numero_dossier']) || !isset($input['id_cadastre']) || !isset($input['libelle']) || !isset($input['date_demande']) || !isset($input['date_limite']) || !isset($input['statut']) || !isset($input['lien_calypso']) || !isset($input['type_dossier']) || !isset($input['sous_type_dossier']) || !isset($input['id_commune'])) {
            echo json_encode(["success" => false, "message" => "Données manquantes pour l'ajout du dossier"]);
            exit;
        }
    
        // Récupère les données de l'input
        $data = [
            'numero_dossier' => $input['numero_dossier'],
            'id_cadastre' => $input['id_cadastre'],
            'libelle' => $input['libelle'],
            'date_demande' => $input['date_demande'],
            'date_limite' => $input['date_limite'],
            'statut' => $input['statut'],
            'lien_calypso' => $input['lien_calypso'],
            'type_dossier' => $input['type_dossier'],
            'sous_type_dossier' => $input['sous_type_dossier'],
            'id_commune' => $input['id_commune']
        ];
    
        // Appelle la méthode pour ajouter le dossier
        $success = $dossierModel->addDossier($data);
        // Retourne une réponse JSON
        echo json_encode(['success' => $success]);
        break;
    
    case 'deleteDossier':
        // Vérifie que l'ID du dossier est présent
        if (!isset($input['id_dossier'])) {
            echo json_encode(["success" => false, "message" => "ID du dossier manquant"]);
            exit;
        }
        $id_dossier = $input['id_dossier'];
        // Appelle la méthode pour supprimer le dossier
        $success = $dossierModel->deleteDossier($id_dossier);
        // Retourne la réponse
        echo json_encode(["success" => $success, "message" => $success ? "Dossier supprimé avec succès" : "Erreur lors de la suppression du dossier"]);
        break;

    case 'getDossiersByUser':
        // Récupérer les données envoyées via POST
        $inputData = json_decode(file_get_contents('php://input'), true);
        // Vérifier si le userId est bien envoyé dans le corps de la requête
        if (isset($inputData['userId'])) {
            $userId = $inputData['userId'];  // Récupérer l'ID utilisateur à partir des données POST
            $success = $dossierModel->getDossiersByUser($userId);
            
            if ($success) {
                echo json_encode(['success' => true, 'dossiers' => $success]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Aucun dossier trouvé pour cet utilisateur.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action non valide"]);
        break;
}

?>
