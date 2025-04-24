<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

class UserModel {
    private $pdo;
    private $logCollection;

    public function __construct($pdo, $logCollection) {
        $this->pdo = $pdo;
        $this->logCollection = $logCollection;
    }

    // 🔓 Connecter l'utilisateur
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
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
    
            // Stocke l'email dans la session
            $_SESSION['email'] = $email; 

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
    
    // 📌 Récupérer les données de l'utilisateur connecté
    public function getUserById($id) {
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

    // 📌 Récupérer tous les utilisateurs
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

    // ➕ Ajouter un utilisateur
    public function registerUser($nom, $prenom, $email, $password, $annee_naissance, $pseudo, $genre, $poste) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, password, annee_naissance, pseudo, genre, poste) 
                VALUES (:nom, :prenom, :email, :password, :annee_naissance, :pseudo, :genre, :poste)");
    
            $success = $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'password' => $hashedPassword,
                'annee_naissance' => $annee_naissance,
                'pseudo' => $pseudo,
                'genre' => $genre,
                'poste' => $poste
            ]);
    
            if ($success) {
                // Log l'action dans MongoDB
                $logData = [
                    'action' => 'Nouvel utilisateur enregistré',
                    'email' => $email,
                    'pseudo' => $pseudo,
                    'poste' => $poste,
                    'date' => date("c"),
                ];
                $this->logCollection->insertOne($logData);
    
                return ['success' => true, 'message' => 'Utilisateur enregistré avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l’enregistrement.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()];
        }
    }

    // ✏️ Mettre à jour les données d'un utilisateur
    public function updateUser($id_utilisateur, $nom, $prenom, $email, $annee_naissance, $pseudo, $genre, $poste) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs SET 
                nom = :nom, prenom = :prenom, email = :email, annee_naissance = :annee_naissance, 
                pseudo = :pseudo, genre = :genre, poste = :poste 
                WHERE id_utilisateur = :id_utilisateur");
    
            $success = $stmt->execute([
                'id_utilisateur' => $id_utilisateur,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'annee_naissance' => $annee_naissance,
                'pseudo' => $pseudo,
                'genre' => $genre,
                'poste' => $poste
            ]);
    
            return $success;
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()];
        }
    }

    // 🔒 Changer le mot de passe
    public function updatePassword($id_utilisateur, $nouveau_mot_de_passe) {
        // Hachage du nouveau mot de passe
        $hashed_password = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
        // Mise à jour du mot de passe dans MySQL
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET password = :password WHERE id_utilisateur = :id_utilisateur");
        $stmt->execute([
            'password' => $hashed_password,
            'id_utilisateur' => $id_utilisateur
        ]);
        // Récupération de l'email de l'utilisateur pour le log
        $stmt = $this->pdo->prepare("SELECT email FROM utilisateurs WHERE id_utilisateur = :id_utilisateur");
        $stmt->execute(['id_utilisateur' => $id_utilisateur]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {

            $logData = [
                'email' => $user['email'],
                'action' => 'changement de mot de passe',
                'date' => date("c"),
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            $this->logCollection->insertOne($logData);
        }
        return ["success" => true, "message" => "Mot de passe mis à jour avec succès"];
    }
    
    // ❌ Déconnexion
    public function logoutUser($email) {
        // Détruire la session
        session_unset();
        session_destroy();
    
        // Enregistrer l'événement dans MongoDB
        $logData = [
            'email' => $email,
            'action' => 'logout',
            'date' => date("c"),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->logCollection->insertOne($logData);
    
        return ["success" => true, "message" => "Déconnexion réussie"];
    }
    
    
        
}
?>
