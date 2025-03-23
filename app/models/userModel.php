<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php'; // Charger MongoDB

class UserModel {
    private $pdo;
    private $logCollection;

    public function __construct($pdo, $logCollection) {
        $this->pdo = $pdo;
        $this->logCollection = $logCollection;
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nom_role 
            FROM utilisateurs u
            LEFT JOIN roles r ON u.id_role = r.id_role
            WHERE u.email = :email
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Log l'événement de connexion dans MongoDB
            $logData = [
                'email' => $email,
                'action' => 'login',
                'date' => date("c"),
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            $this->logCollection->insertOne($logData);
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        // Ne jamais exposer de données sensibles comme les mots de passe
        $stmt = $this->pdo->prepare("
            SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.annee_naissance, 
                   u.pseudo, u.genre, u.poste, r.nom_role 
            FROM utilisateurs u
            LEFT JOIN roles r ON u.id_role = r.id_role
            WHERE u.id_utilisateur = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("
            SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.annee_naissance, 
                   u.pseudo, u.genre, u.poste, r.nom_role 
            FROM utilisateurs u
            LEFT JOIN roles r ON u.id_role = r.id_role
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
