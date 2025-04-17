<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/communeModel.php';
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
// Fichier de log pour debug
define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
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


$communeModel = new CommuneModel($pdo, $modificationCollection);
$data = json_decode(file_get_contents("php://input"), true);

// 🔹 Vérifier si $data contient bien les infos attendues
if (!$data) {
    echo json_encode(["success" => false, "message" => "Données JSON invalides"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'searchCommune':
            $conditions = [];
            $params = [];
        
            if (!empty($data['code_commune'])) {
                $conditions[] = "code_commune = :code_commune";
                $params['code_commune'] = $data['code_commune'];
            }
            if (!empty($data['cp_commune'])) {
                $conditions[] = "cp_commune = :cp_commune";
                $params['cp_commune'] = $data['cp_commune'];
            }
            if (!empty($data['id_commune'])) {
                $conditions[] = "id_commune LIKE :id_commune";
                $params['id_commune'] = $data['id_commune'];
            }
            
        
            // Si aucune condition n'est ajoutée, empêcher la recherche vide
            if (empty($conditions)) {
                echo json_encode(["success" => false, "message" => "Aucun critère de recherche fourni"]);
                exit;
            }
        
            $sql = "SELECT * FROM communes WHERE " . implode(" AND ", $conditions);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            echo json_encode($results);
            exit;

        case 'getCommunes':
            // Vérifie que la connexion à la base de données fonctionne
            $communes = $communeModel->getAllCommunes();  // Appel à la méthode pour récupérer toutes les communes
            
            if ($communes) {
                echo json_encode(['success' => true, 'communes' => $communes]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Aucune commune trouvée']);
                exit;
            }
            break;

        case 'getCommune':
            if (!isset($data['id_commune'])) {
                echo json_encode(["success" => false, "message" => "ID de la commune manquant"]);
                exit;
            }
            $result = $communeModel->getCommuneById($data['id_commune']);
            echo json_encode($result);
            exit;

        case 'addCommune':
            // Vérifie si l'utilisateur est authentifié
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
                exit;
            }

            // Vérifie la présence de toutes les données requises
            if (!isset($data['code_commune'], $data['nom_commune'], $data['cp_commune'], $data['email_commune'], $data['tel_commune'], $data['adresse_commune'], $data['contact'], $data['reseau_instruction'], $data['urbaniste_vra'])) {
                echo json_encode(["success" => false, "message" => "Données incomplètes"]);
                exit;
            }

            // Appelle la fonction du modèle pour ajouter la commune
            $communeModel->addCommune($data);
            echo json_encode(["success" => true, "message" => "Commune ajoutée avec succès"]);
            exit;
    
        case 'updateCommune':
            if (!isset($data['id_commune'])) {
                echo json_encode(["success" => false, "message" => "ID de la commune manquant"]);
                exit;
            }
            $success = $communeModel->updateCommune($data);
            echo json_encode(["success" => $success, "message" => $success ? "Mise à jour réussie" : "Erreur de mise à jour"]);
            exit;
        
        case 'deleteCommune':
            if (!isset($data['id_commune'])) {
                echo json_encode(["success" => false, "message" => "ID de la commune manquant"]);
                exit;
            }
            $success = $communeModel->deleteCommune($data['id_commune']);
            echo json_encode(["success" => $success, "message" => $success ? "Suppression réussie" : "Erreur de suppression"]);
            exit;
                
        default:
            echo json_encode(["success" => false, "message" => "Action non définie"]);
            exit;
    }
}


// 🔹 Si aucune action valide n'a été détectée
echo json_encode(["success" => false, "message" => "Requête invalide"]);
exit;
?>
?>
