<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../models/gepModel.php';
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

// Instanciation du modèle GepModel
$gepModel = new GepModel($pdo);
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
        // 🔍 Rechercher des règlements de GEP
        case 'searchGep':
            $conditions = [];
            $params = [];
        
            if (!empty($data['nom_commune'])) {
                $conditions[] = "nom_commune = :nom_commune";
                $params['nom_commune'] = $data['nom_commune'];
            }
            if (!empty($data['section'])) {
                $conditions[] = "section LIKE :section";
                $params['section'] = "%" . $data['section'] . "%";
            }
            if (!empty($data['numero'])) {
                $conditions[] = "numero = :numero";
                $params['numero'] = $data['numero'];
            }
        
            // Si aucune condition n'est ajoutée, empêcher la recherche vide
            if (empty($conditions)) {
                echo json_encode(["success" => false, "message" => "Aucun critère de recherche fourni"]);
                exit;
            }
        
            $sql = "SELECT * FROM gep WHERE " . implode(" AND ", $conditions);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            echo json_encode($results);
            exit;
        // 📌 Récupérer les noms des communes concernées
        case 'getNomCommunes':
            try {
                $nomCommunes = $gepModel->getNomCommunes();
                echo json_encode(["success" => true, "nom_communes" => $nomCommunes]);
                exit;
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
                exit;
            }
            break;
        // Cas par défault  
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
