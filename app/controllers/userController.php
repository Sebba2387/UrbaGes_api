<?php
session_start(); // Démarrer la session

require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../config/mongo.php';  // Inclusion de la configuration MongoDB

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

$pdo = new PDO("mysql:host=localhost;dbname=urbages_db;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$userModel = new UserModel($pdo);
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if ($data['action'] === 'login') {
        $user = $userModel->login($data['email'], $data['password']);
        if ($user) {
            // Connexion réussie, enregistrer l'événement dans MongoDB
            $result = $logCollection->insertOne([
                'email' => $data['email'],
                'action' => 'login',
                'date' => date("c"),  // Date actuelle au format ISO 8601 (ex : 2025-03-22T14:45:00+00:00)
                'ip' => $_SERVER['REMOTE_ADDR'],  // Enregistre l'adresse IP
            ]);

            if ($result->getInsertedCount() > 0) {
                echo json_encode(["success" => true, "user" => $user, "log" => "Log enregistré"]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de l'enregistrement du log"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $user = $userModel->getUserById($_GET['id']);
    if ($user) {
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Utilisateur non trouvé"]);
    }
}
exit;
