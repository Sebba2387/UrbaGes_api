<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';

class CourrierModel {
    private $pdo;
    private $modificationCollection;

    public function __construct($pdo, $modificationCollection) {
        $this->pdo = $pdo;
        $this->modificationCollection = $modificationCollection;
    }

    // 🔍 Rechercher un courrier
    public function searchCourrier($filters) {
        $sql = "SELECT * FROM courriers WHERE 1=1";

        $params = [];

        if (!empty($filters['id_courrier'])) {
            $sql .= " AND id_courrier = :id_courrier";
            $params[':id_courrier'] = $filters['id_courrier'];
        }
        if (!empty($filters['type_courrier'])) {
            $sql .= " AND type_courrier = :type_courrier";
            $params[':type_courrier'] = $filters['type_courrier'];
        }
        if (!empty($filters['libelle_courrier'])) {
            $sql .= " AND libelle_courrier LIKE :libelle_courrier";
            $params[':libelle_courrier'] = "%" . $filters['libelle_courrier'] . "%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 Récupérer les données d'un courrier
    public function getCourrierById($id_courrier) {
        $sql = "SELECT id_courrier, type_courrier, libelle_courrier, corps_courrier
                FROM courriers
                WHERE id_courrier = :id_courrier";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_courrier', $id_courrier, PDO::PARAM_INT);
        $stmt->execute();
        $courrier = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return [
            "courrier" => $courrier
        ];
    }

    // ✏️ Mettre à jour un courrier
    public function updateCourrier($data) {
        $sql = "UPDATE courriers SET 
                    type_courrier = :type_courrier,
                    libelle_courrier = :libelle_courrier,
                    corps_courrier = :corps_courrier
                WHERE id_courrier = :id_courrier";
    
        $stmt = $this->pdo->prepare($sql);
    
        $success = $stmt->execute([
            ':type_courrier' => $data['type_courrier'],
            ':libelle_courrier' => $data['libelle_courrier'],
            ':corps_courrier' => $data['corps_courrier'],
            ':id_courrier' => $data['id_courrier']
        ]);
    
        if ($success) {
            $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'inconnu';
    
            $logData = [
                'action' => 'Mise à jour d\'un courrier type',
                'courrier' => $data['id_courrier'],
                'type' => $data['type_courrier'],
                'libelle' => $data['libelle_courrier'],
                'email' => $email,
                'date' => date("c")
            ];
    
            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }

    // ➕ Ajouter un courrier
    public function addCourrier($data) {
        $sql = "INSERT INTO courriers (type_courrier, libelle_courrier, corps_courrier)
                VALUES (:type_courrier, :libelle_courrier, :corps_courrier)";
        
        $stmt = $this->pdo->prepare($sql);
    
        $success = $stmt->execute([
            ':type_courrier' => $data['type_courrier'],
            ':libelle_courrier' => $data['libelle_courrier'],
            ':corps_courrier' => $data['corps_courrier']
        ]);
    
        if ($success) {
            $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'inconnu';
    
            $logData = [
                'action' => 'Ajout d\'un courrier type',
                'type' => $data['type_courrier'],
                'libelle' => $data['libelle_courrier'],
                'email' => $email,
                'date' => date("c")
            ];
    
            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }
    
    // 🗑️ Supprimer un courrier
    public function deleteCourrier($id_courrier) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'inconnu';
    
        $stmt = $this->pdo->prepare("SELECT type_courrier, libelle_courrier FROM courriers WHERE id_courrier = :id_courrier");
        $stmt->bindParam(':id_courrier', $id_courrier, PDO::PARAM_INT);
        $stmt->execute();
        $courrier = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$courrier) {
            return false;
        }
    
        $sql = "DELETE FROM courriers WHERE id_courrier = :id_courrier";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_courrier', $id_courrier, PDO::PARAM_INT);
        $success = $stmt->execute();
    
        if ($success) {
            $logData = [
                'action' => 'Suppression d\'un courrier type',
                'courrier' => $id_courrier,
                'type' => $courrier['type_courrier'],
                'libelle' => $courrier['libelle_courrier'],
                'email' => $email,
                'date' => date("c")
            ];
    
            $this->modificationCollection->insertOne($logData);
        }
    
        return $success;
    }

    // ⚙️ Générer un courrier
    public function getDonneesCourrierAvecDossier($id_courrier, $id_dossier) {
        // Récupération du courrier
        $queryCourrier = $this->pdo->prepare("SELECT * FROM courriers WHERE id_courrier = :id_courrier");
        $queryCourrier->execute(['id_courrier' => $id_courrier]);
        $courrier = $queryCourrier->fetch(PDO::FETCH_ASSOC);

        // Récupération du dossier
        $queryDossier = $this->pdo->prepare("SELECT * FROM dossiers WHERE id_dossier = :id_dossier");
        $queryDossier->execute(['id_dossier' => $id_dossier]);
        $dossier = $queryDossier->fetch(PDO::FETCH_ASSOC);

        // Vérification de l'existence
        if (!$courrier || !$dossier) {
            return null;
        }

        // Retourne les données combinées
        return [
            'courrier' => $courrier,
            'dossier' => $dossier
        ];
    }
    
}
