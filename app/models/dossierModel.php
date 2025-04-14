<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

class DossierModel {
    private $pdo;
    private $modificationCollection;

    public function __construct($pdo, $mongoConfig) {
        $this->pdo = $pdo;
        $this->modificationCollection = $mongoConfig;
    }

    public function searchDossier($filters) {
        $sql = "SELECT d.*, c.nom_commune, u.pseudo 
                FROM dossiers d
                JOIN communes c ON d.id_commune = c.id_commune
                JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                WHERE 1=1";

        $params = [];

        if (!empty($filters['nom_commune'])) {
            $sql .= " AND c.nom_commune LIKE :nom_commune";
            $params[':nom_commune'] = "%" . $filters['nom_commune'] . "%";
        }
        if (!empty($filters['numero_dossier'])) {
            $sql .= " AND d.numero_dossier LIKE :numero_dossier";
            $params[':numero_dossier'] = "%" . $filters['numero_dossier'] . "%";
        }
        if (!empty($filters['id_cadastre'])) {
            $sql .= " AND d.id_cadastre LIKE :id_cadastre";
            $params[':id_cadastre'] = "%" . $filters['id_cadastre'] . "%";
        }
        if (!empty($filters['type_dossier'])) {
            $sql .= " AND d.type_dossier = :type_dossier";
            $params[':type_dossier'] = $filters['type_dossier'];
        }
        if (!empty($filters['sous_type_dossier'])) {
            $sql .= " AND d.sous_type_dossier = :sous_type_dossier";
            $params[':sous_type_dossier'] = $filters['sous_type_dossier'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📄 Récupérer un dossier par son ID
    public function getDossierById($id_dossier) {
        // Récupération du dossier avec le pseudo lié
        $sql = "SELECT d.id_dossier, u.pseudo, c.nom_commune, d.numero_dossier, d.type_dossier, d.sous_type_dossier, d.id_cadastre, d.libelle, d.date_demande, d.date_limite, d.statut, d.lien_calypso, d.id_utilisateur
                FROM dossiers d
                JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                JOIN communes c ON d.id_commune = c.id_commune 
                WHERE d.id_dossier = :id_dossier";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_dossier', $id_dossier, PDO::PARAM_INT);
        $stmt->execute();
        $dossier = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Récupération de tous les pseudos pour la liste
        $sqlUsers = "SELECT id_utilisateur, pseudo FROM utilisateurs";
        $stmtUsers = $this->pdo->prepare($sqlUsers);
        $stmtUsers->execute();
        $utilisateurs = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
    
        return [
            "dossier" => $dossier,
            "utilisateurs" => $utilisateurs
        ];
    }

    public function updateDossier($data) {
        // Trouver l'id_utilisateur à partir du pseudo reçu
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE pseudo = :pseudo");
        $stmt->bindParam(":pseudo", $data['pseudo']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            return false; // utilisateur introuvable
        }
    
        $id_utilisateur = $user['id_utilisateur'];
    
        // Mise à jour du dossier avec id_utilisateur
        $sql = "UPDATE dossiers SET 
                    numero_dossier = :numero_dossier,
                    id_cadastre = :id_cadastre,
                    libelle = :libelle,
                    date_demande = :date_demande,
                    date_limite = :date_limite,
                    statut = :statut,
                    lien_calypso = :lien_calypso,
                    type_dossier = :type_dossier,
                    sous_type_dossier = :sous_type_dossier,
                    id_utilisateur = :id_utilisateur
                WHERE id_dossier = :id_dossier";
    
        $stmt = $this->pdo->prepare($sql);
    
        $success = $stmt->execute([
            ':numero_dossier' => $data['numero_dossier'],
            ':id_cadastre' => $data['id_cadastre'],
            ':libelle' => $data['libelle'],
            ':date_demande' => $data['date_demande'],
            ':date_limite' => $data['date_limite'],
            ':statut' => $data['statut'],
            ':lien_calypso' => $data['lien_calypso'],
            ':type_dossier' => $data['type_dossier'],
            ':sous_type_dossier' => $data['sous_type_dossier'],
            ':id_utilisateur' => $id_utilisateur,
            ':id_dossier' => $data['id_dossier']
        ]);
    
        if ($success) {
            $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'inconnu'; 

            $logData = [
                'action' => 'Mise à jour des données de dossier',
                'dossier' => $data['id_dossier'],
                'type' => $data['type_dossier'],
                'mission' => $data['sous_type_dossier'],
                'email' => $email,
                'date' => date("c")
            ];

            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }

    public function getAllCommunes() {
        // Prépare la requête pour récupérer toutes les communes
        $sql = "SELECT id_commune, nom_commune FROM communes";
        $stmt = $this->pdo->query($sql);
        $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        return $communes;
    }
    

    public function addDossier($data) {
        // Prépare la requête SQL pour insérer un nouveau dossier
        $sql = "INSERT INTO dossiers 
                (numero_dossier, id_cadastre, libelle, date_demande, date_limite, statut, lien_calypso, type_dossier, sous_type_dossier, id_utilisateur, id_commune) 
                VALUES 
                (:numero_dossier, :id_cadastre, :libelle, :date_demande, :date_limite, :statut, :lien_calypso, :type_dossier, :sous_type_dossier, :id_utilisateur, :id_commune)";
    
        $stmt = $this->pdo->prepare($sql);
    
        // Récupère l'email de l'utilisateur connecté dans la session
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
        if (!$email) {
            throw new Exception("Utilisateur non connecté");
        }
    
        // Récupère l'ID de l'utilisateur à partir de son email
        $stmtUser = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email");
        $stmtUser->execute(['email' => $email]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            throw new Exception("Utilisateur non trouvé");
        }
    
        // Exécute l'insertion du dossier
        $success = $stmt->execute([
            ':numero_dossier' => $data['numero_dossier'],
            ':id_cadastre' => $data['id_cadastre'],
            ':libelle' => $data['libelle'],
            ':date_demande' => $data['date_demande'],
            ':date_limite' => $data['date_limite'],
            ':statut' => $data['statut'],
            ':lien_calypso' => $data['lien_calypso'],
            ':type_dossier' => $data['type_dossier'],
            ':sous_type_dossier' => $data['sous_type_dossier'],
            ':id_utilisateur' => $user['id_utilisateur'],  // Utilisateur connecté
            ':id_commune' => $data['id_commune']
        ]);
    
        // Si l'ajout du dossier est réussi, on log l'événement dans MongoDB
        if ($success) {
            $logData = [
                'action' => 'Ajout d\'un dossier',
                'dossier' => $data['numero_dossier'],
                'type' => $data['type_dossier'],
                'mission' => $data['sous_type_dossier'],
                'email' => $email,  // Email de l'utilisateur connecté
                'date' => date("c")
            ];
            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }

    public function deleteDossier($id_dossier) {
        // Trouver l'email de l'utilisateur connecté depuis la session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'inconnu'; // Récupère l'email de l'utilisateur connecté
        // Récupérer les détails du dossier avant suppression
        $stmt = $this->pdo->prepare("SELECT type_dossier, sous_type_dossier FROM dossiers WHERE id_dossier = :id_dossier");
        $stmt->bindParam(':id_dossier', $id_dossier, PDO::PARAM_INT);
        $stmt->execute();
        $dossier = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dossier) {
            return false; 
        }
        // Suppression du dossier
        $sql = "DELETE FROM dossiers WHERE id_dossier = :id_dossier";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_dossier', $id_dossier, PDO::PARAM_INT);
        $success = $stmt->execute();
        if ($success) {
            // Loggez l'action dans MongoDB après la suppression
            $logData = [
                'action' => 'Suppression d\'un dossier',
                'dossier' => $id_dossier,
                'type' => $dossier['type_dossier'],  // type_dossier du dossier
                'mission' => $dossier['sous_type_dossier'],  // sous_type_dossier du dossier
                'email' => $email,  // Email de l'utilisateur connecté
                'date' => date("c")
            ];
    
            // Enregistrement du log dans MongoDB
            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }

    public function getDossiersByUser($userId) {
        $query = "SELECT d.*, c.nom_commune 
              FROM dossiers d
              JOIN communes c ON d.id_commune = c.id_commune
              WHERE d.id_utilisateur = :userId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $dossiers;
    }
}
?>
