<?php
error_log("Je suis dans userApi");
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/controllers/userController.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// Récupérer les données envoyées par la requête
$data = json_decode(file_get_contents("php://input"), true);

// Debug : Affiche ce que reçoit le serveur
var_dump($data);

// Si l'action est 'login', on l'appelle pour gérer l'authentification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'login') {
        // Appel du contrôleur pour vérifier les identifiants
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
}

// Si l'action est 'getAllUsers', on vérifie d'abord l'authentification et le rôle de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'getAllUsers') {
        
        // Vérification du rôle de l'utilisateur connecté
        try {
            $stmt = $pdo->prepare("SELECT nom_role FROM utilisateurs INNER JOIN roles ON utilisateurs.id_role = roles.id_role WHERE utilisateurs.id_utilisateur = :id_utilisateur");
            $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
            $userRole = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Affiche le rôle de l'utilisateur
            var_dump($userRole);

            // Vérifier si l'utilisateur a un rôle 'admin' ou 'moderateur'
            if ($userRole && in_array($userRole['nom_role'], ['admin', 'moderateur'])) {
                // Si l'utilisateur a un rôle valide, on peut appeler la fonction du contrôleur pour récupérer tous les utilisateurs
                require_once __DIR__ . '/../../app/controllers/userController.php';
            } else {
                echo json_encode(["success" => false, "message" => "Accès refusé : rôle insuffisant", "userRole" => $userRole['nom_role']]);
                exit;
            }

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Erreur lors de la vérification du rôle"]);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'registerUser') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'getUser') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'updateUser') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    } elseif ($data['action'] === 'deleteUser') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'searchUsers') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'updatePassword') {
        require_once __DIR__ . '/../../app/controllers/userController.php';
    }
}


?>
