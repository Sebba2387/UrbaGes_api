<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/userModel.php';
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
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log'); // Vérifie que le chemin est correct
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
    
    // 📋 inscription d'un utilisateur via userModel.php
    case 'registerUser':
        // Vérifie si l'utilisateur est authentifié
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
            exit;
        }
        // Vérifie la présence de toutes les données requises
        if (!isset($data['nom'], $data['prenom'], $data['email'], $data['password'], $data['annee_naissance'], $data['pseudo'], $data['genre'], $data['poste'])) {
            echo json_encode(["success" => false, "message" => "Données incomplètes"]);
            exit;
        }
        // Appelle la fonction du modèle pour enregistrer l'utilisateur
        $result = $userModel->registerUser(
            $data['nom'], 
            $data['prenom'], 
            $data['email'], 
            $data['password'], 
            $data['annee_naissance'], 
            $data['pseudo'], 
            $data['genre'], 
            $data['poste']
        );
        echo json_encode($result);
        exit;

    // 👤 Récupération des données d'un utilisateur
    case 'getUser':
        // Vérifie si l'utilisateur est authentifié et a un rôle autorisé
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
            exit;
        }
        // Vérifie le rôle de l'utilisateur
        $stmt = $pdo->prepare("
            SELECT nom_role 
            FROM utilisateurs 
            INNER JOIN roles ON utilisateurs.id_role = roles.id_role 
            WHERE utilisateurs.id_utilisateur = :id_utilisateur
        ");
        $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$userRole || !in_array($userRole['nom_role'], ['admin', 'moderateur'])) {
            echo json_encode(["success" => false, "message" => "Accès refusé"]);
            exit;
        }
        // Vérifie si l'ID de l'utilisateur est fourni pour récupérer les informations
        if (!isset($data['id_utilisateur'])) {
            echo json_encode(["success" => false, "message" => "ID utilisateur manquant"]);
            exit;
        }
        // Récupère les données de l'utilisateur
        $user = $userModel->getUserById($data['id_utilisateur']);
        echo json_encode($user);
        exit;

    // 📋 Mise à jour des données d'un utilisateur via userModel.php
    case 'updateUser':
        // Vérifie si l'utilisateur est authentifié et a un rôle autorisé
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
            exit;
        }
        // Vérifie le rôle de l'utilisateur
        $stmt = $pdo->prepare("
            SELECT nom_role 
            FROM utilisateurs 
            INNER JOIN roles ON utilisateurs.id_role = roles.id_role 
            WHERE utilisateurs.id_utilisateur = :id_utilisateur
        ");
        $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$userRole || !in_array($userRole['nom_role'], ['admin', 'moderateur'])) {
            echo json_encode(["success" => false, "message" => "Accès refusé"]);
            exit;
        }
        // Vérifie la présence de toutes les données requises pour la mise à jour
        if (!isset($data['id_utilisateur'], $data['nom'], $data['prenom'], $data['email'], $data['annee_naissance'], $data['pseudo'], $data['genre'], $data['poste'])) {
            echo json_encode(["success" => false, "message" => "Données incomplètes"]);
            exit;
        }
        // Met à jour les informations de l'utilisateur
        $result = $userModel->updateUser(
            $data['id_utilisateur'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['annee_naissance'],
            $data['pseudo'],
            $data['genre'],
            $data['poste']
        );
        // Envoie la réponse avec succès ou erreur
        if ($result) {
            echo json_encode(["success" => true, "message" => "Mise à jour réussie !"]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour"]);
        }
        exit;

    // 🗑️ Supprimer un utilisateur
    case 'deleteUser':
        // Vérifie si l'utilisateur est authentifié et a un rôle autorisé
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
            exit;
        }
        // Vérifie le rôle de l'utilisateur
        $stmt = $pdo->prepare("
            SELECT nom_role 
            FROM utilisateurs 
            INNER JOIN roles ON utilisateurs.id_role = roles.id_role 
            WHERE utilisateurs.id_utilisateur = :id_utilisateur
        ");
        $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$userRole || !in_array($userRole['nom_role'], ['admin', 'moderateur'])) {
            echo json_encode(["success" => false, "message" => "Accès refusé"]);
            exit;
        }
    
        // Vérifie que l'ID utilisateur est passé et est valide
        if (!isset($data['id_utilisateur'])) {
            echo json_encode(["success" => false, "message" => "ID utilisateur manquant"]);
            exit;
        }
    
        // Supprime l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = :id_utilisateur");
        $stmt->bindParam(':id_utilisateur', $data['id_utilisateur'], PDO::PARAM_INT);
        $stmt->execute();
    
        // Vérifie si la suppression a été effectuée
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Utilisateur supprimé avec succès"]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la suppression"]);
        }
        exit;

    // 🔍 Rechercher un utilisateur
    case 'searchUsers':
        $queryNom = isset($data['nom']) ? '%' . $data['nom'] . '%' : '%';
        $queryPrenom = isset($data['prenom']) ? '%' . $data['prenom'] . '%' : '%';
        $queryPoste = isset($data['poste']) ? '%' . $data['poste'] . '%' : '%';
    
        $stmt = $pdo->prepare("
            SELECT id_utilisateur, nom, prenom, email, annee_naissance, pseudo, genre, poste
            FROM utilisateurs
            WHERE nom LIKE :nom
            AND prenom LIKE :prenom
            AND poste LIKE :poste
        ");
        $stmt->bindParam(':nom', $queryNom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $queryPrenom, PDO::PARAM_STR);
        $stmt->bindParam(':poste', $queryPoste, PDO::PARAM_STR);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(["success" => true, "users" => $users]);
        exit;
    
    // Changement de mot de passe
    case 'updatePassword':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
            exit;
        }
    
        if (!isset($data['ancien_mot_de_passe'], $data['nouveau_mot_de_passe'])) {
            echo json_encode(["success" => false, "message" => "Données incomplètes"]);
            exit;
        }
    
        $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE id_utilisateur = :id_utilisateur");
        $stmt->execute(['id_utilisateur' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user || !password_verify($data['ancien_mot_de_passe'], $user['password'])) {
            echo json_encode(["success" => false, "message" => "Ancien mot de passe incorrect"]);
            exit;
        }
    
        $result = $userModel->updatePassword($_SESSION['user_id'], $data['nouveau_mot_de_passe']);
        echo json_encode($result);
        exit;
        
    
    // ❌ Action inconnue
    default:
        echo json_encode(["success" => false, "message" => "Action non valide"]);
        exit;
}
?>
