<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/communeModel.php';
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
            if (!empty($data['nom_commune'])) {
                $conditions[] = "nom_commune LIKE :nom_commune";
                $params['nom_commune'] = "%" . $data['nom_commune'] . "%";
            }
            if (!empty($data['cp_commune'])) {
                $conditions[] = "cp_commune = :cp_commune";
                $params['cp_commune'] = $data['cp_commune'];
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
    }
}


// 🔹 Si aucune action valide n'a été détectée
echo json_encode(["success" => false, "message" => "Requête invalide"]);
exit;
?>
?>
