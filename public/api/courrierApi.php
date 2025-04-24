<?php
// Vérification et démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS (évite les blocages cross-origin)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Vérification de l'authentification (si l'utilisateur est connecté)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utilisateur non authentifié"]);
    exit;
}

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../../app/controllers/courrierController.php';

// Lecture des données brutes JSON envoyées, puis décodage en tableau associatif PHP
$data = json_decode(file_get_contents("php://input"), true);

// Vérification si $data contient bien les infos attendues (JSON)
if (!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Aucune action reçue"]);
    exit;
}

// Vérification que la requête est de type POST et de l'action définie dans les données reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action'])) {
    if (
        $data['action'] === 'addCourrier' ||
        $data['action'] === 'deleteCourrier' ||
        $data['action'] === 'searchCourrier' ||
        $data['action'] === 'updateCourrier' ||
        $data['action'] === 'getCourrierById' ||
        $data['action'] === 'genererCourrier'
    ) {
        require_once __DIR__ . '/../../app/controllers/courrierController.php';
    } else {
        echo json_encode(["success" => false, "message" => "Action inconnue"]);
        exit;
    }
}

// Fichier de log pour debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// error_log(print_r($data, true));
?>
