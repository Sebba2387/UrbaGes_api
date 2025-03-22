<?php
session_start();
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../config/mongo.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Instanciation du modèle
$userModel = new UserModel($pdo, $logCollection);
$data = json_decode(file_get_contents("php://input"), true);

// Vérification de l'action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'login') {
        $user = $userModel->login($data['email'], $data['password']);
        if ($user) {
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
        }
    }

    if ($data['action'] === 'getProfile' && isset($data['userId'])) {
        // Vérifier si l'utilisateur est autorisé à accéder à ce profil
        $user = $userModel->getUserById($data['userId']);
        if ($user) {
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Utilisateur non trouvé"]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide"]);
}
exit;
?>
