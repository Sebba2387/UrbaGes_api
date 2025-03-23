<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Assurez-vous que la session est démarrée
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../config/mongo.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fichier de log pour debug
define('DEBUG_LOG', __DIR__ . '/../../logs/debug.log');
file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Requête reçue : " . file_get_contents("php://input") . "\n", FILE_APPEND);

// CORS (pour éviter les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérification de la connexion à la base de données
if (!isset($pdo) || !$pdo) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

// Instanciation du modèle utilisateur
$userModel = new UserModel($pdo, $logCollection);
$data = json_decode(file_get_contents("php://input"), true);

// Vérification si les données sont bien envoyées
if (!$data || !isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Données invalides"]);
    exit;
}

// Récupération de l'action demandée
$action = $data['action'];

switch ($action) {
    
    // 🔐 Connexion utilisateur
    case 'login':
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(["success" => false, "message" => "Email et mot de passe requis"]);
            exit;
        }

        $user = $userModel->login($data['email'], $data['password']);
        if ($user) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
        }
        exit;

    // 👤 Récupération du profil utilisateur
    case 'getProfile':
        if (!isset($data['userId'])) {
            echo json_encode(["success" => false, "message" => "ID utilisateur requis"]);
            exit;
        }

        $user = $userModel->getUserById($data['userId']);
        if ($user) {
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Utilisateur non trouvé"]);
        }
        exit;

    // 📋 Récupération de tous les utilisateurs via userModel.php
    case 'getAllUsers':
        try {
            // Vérification si l'utilisateur est connecté et a un rôle admin ou moderateur
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
                exit;
            }

            // Récupération des informations de l'utilisateur connecté
            $stmt = $pdo->prepare("SELECT nom_role FROM utilisateurs INNER JOIN roles ON utilisateurs.id_role = roles.id_role WHERE utilisateurs.id_utilisateur = :id_utilisateur");
            $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
            $userRole = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userRole || !in_array($userRole['nom_role'], ['admin', 'moderateur'])) {
                echo json_encode(["success" => false, "message" => "Accès refusé : rôle insuffisant"]);
                exit;
            }

            // Si l'utilisateur est un admin ou un modérateur, on continue avec la récupération des utilisateurs
            $users = $userModel->getAllUsers();

            if (!$users) {
                echo json_encode(["success" => false, "message" => "Aucun utilisateur trouvé"]);
                exit;
            }

            echo json_encode(["success" => true, "users" => $users, "userRole" => $userRole['nom_role']]);
        } catch (Exception $e) {
            error_log("Erreur SQL: " . $e->getMessage());
            file_put_contents(DEBUG_LOG, date("Y-m-d H:i:s") . " - Erreur SQL: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des utilisateurs"]);
        }
        exit;

    // 🔴 Déconnexion
    case 'logout':
        session_unset();
        session_destroy();
        echo json_encode(["success" => true, "message" => "Déconnexion réussie"]);
        exit;

    // ❌ Action inconnue
    default:
        echo json_encode(["success" => false, "message" => "Action non valide"]);
        exit;
}
?>
