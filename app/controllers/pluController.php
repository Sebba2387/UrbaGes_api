<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/pluModel.php';
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

$pluModel = new PluModel($pdo, $modificationCollection);
$data = json_decode(file_get_contents("php://input"), true);

// 🔹 Vérifier si $data contient bien les infos attendues
if (!$data) {
    echo json_encode(["success" => false, "message" => "Données JSON invalides"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {

        // Recherche un PLU en fonction code_commune, nom_commune, cp_commune, etat_plu
        case 'searchPlu':
            // Assurez-vous que cette section est bien exécutée lorsque l'action est 'searchPlu'
            $conditions = [];
            $params = [];
        
            // Vérification des données envoyées pour la recherche
            if (!empty($data['code_commune'])) {
                $conditions[] = "communes.code_commune LIKE :code_commune";
                $params['code_commune'] = "%" . $data['code_commune'] . "%";
            }
            if (!empty($data['nom_commune'])) {
                $conditions[] = "communes.nom_commune LIKE :nom_commune";
                $params['nom_commune'] = "%" . $data['nom_commune'] . "%";
            }
            if (!empty($data['cp_commune'])) {
                $conditions[] = "communes.cp_commune LIKE :cp_commune";
                $params['cp_commune'] = "%" . $data['cp_commune'] . "%";
            }
            if (!empty($data['etat_plu'])) {
                $conditions[] = "plu.etat_plu LIKE :etat_plu";
                $params['etat_plu'] = "%" . $data['etat_plu'] . "%";
            }
        
            // Si aucune condition n'est ajoutée, empêcher la recherche vide
            if (empty($conditions)) {
                echo json_encode(["success" => false, "message" => "Aucun critère de recherche fourni"]);
                exit;
            }
        
            // Construction de la requête SQL
            $sql = "SELECT plu.*, communes.code_commune, communes.nom_commune, communes.cp_commune 
                    FROM plu 
                    JOIN communes ON plu.id_commune = communes.id_commune
                    WHERE " . implode(" AND ", $conditions);
            
            $stmt = $pdo->prepare($sql);
            if (!$stmt->execute($params)) {
                echo json_encode(["success" => false, "message" => "Erreur SQL", "error_details" => $stmt->errorInfo()]);
                exit;
            }
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            echo json_encode($results);
            exit;
        
        case 'getPluById':
            if (isset($data['id_plu'])) {
                $plu = $pluModel->getPluById($data['id_plu']);
                if ($plu) {
                    echo json_encode(["success" => true, "plu" => $plu]);
                } else {
                    echo json_encode(["success" => false, "message" => "PLU non trouvé"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "ID PLU manquant"]);
            }
            break;
            
        case 'updatePlu':
            error_log("Données reçues : " . json_encode($data));
        
            $requiredFields = ['id_plu', 'type_plu', 'etat_plu', 'date_plu', 'systeme_ass', 'statut_zonage', 
            'statut_pres', 'date_annexion', 'lien_zonage', 'lien_dhua', 'observation_plu'];
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $data) || $data[$field] === null) {
                    echo json_encode(["success" => false, "message" => "Données manquantes : " . $field]);
                    exit;
                }
            }
        
            $updated = $pluModel->updatePlu($data);
            if ($updated) {
                echo json_encode(["success" => true, "message" => "PLU mis à jour avec succès"]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour"]);
            }
            break;


        default:
        echo json_encode(["success" => false, "message" => "Action inconnue"]);
        break;
    }
}



?>