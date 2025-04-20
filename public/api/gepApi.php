<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/controllers/gepController.php';

error_reporting(E_ALL);
ini_set('display_errors', 1); // Affiche les erreurs directement

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Fichier de log pour debug
define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Requête reçue : " . file_get_contents("php://input") . "\n", FILE_APPEND);

// Lecture des données envoyées via POST
$data = json_decode(file_get_contents("php://input"), true);

// Si les données sont incorrectes ou absentes
if ($data === null) {
    echo json_encode(["success" => false, "message" => "Données de requête invalides"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'searchGep':
            require_once __DIR__ . '/../../app/controllers/gepController.php';
            break;

        case 'getNomCommunes':
            try {
                $nomCommunes = $gepController->getNomCommunes();
                // Assurez-vous que la réponse est correctement formée avant de l'envoyer
                echo json_encode(["success" => true, "nom_communes" => $nomCommunes]);
                exit;
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des communes : " . $e->getMessage()]);
                exit; 
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Action inconnue"]);
            exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Aucune action spécifiée"]);
    exit;
}

// Activer le mode debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
error_log(print_r($data, true));  // Affiche les données dans le fichier de log PHP
?>
