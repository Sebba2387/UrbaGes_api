<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';
class CommuneModel {
    private $pdo;
    private $modificationCollection;

    public function __construct($pdo, $mongoConfig) {
        $this->pdo = $pdo;
        $this->modificationCollection = $mongoConfig; 
    }

    // 🔍 Rechercher les communes
    public function searchCommunes($code_commune, $id_commune, $cp_commune) {
        $query = "SELECT * FROM communes WHERE 1=1";
        $params = [];
    
        if (!empty($code_commune)) {
            $query .= " AND code_commune LIKE :code_commune";
            $params['code_commune'] = "%$code_commune%";
        }
        if (!empty($cp_commune)) {
            $query .= " AND cp_commune LIKE :cp_commune";
            $params['cp_commune'] = "%$cp_commune%";
        }
        if (!empty($id_commune)) {
            $query .= " AND id_commune LIKE :id_commune";
            $params['id_commune'] = "%$id_commune%";
        }
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 Récupérer les noms de toutes les communes 
    public function getAllCommunes() {
        $sql = "SELECT id_commune, nom_commune FROM communes";
        $stmt = $this->pdo->query($sql);
        $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        return $communes;
    }

    // ➕ Ajouter une commune
    public function addCommune($data) {
        $sql = "INSERT INTO communes (code_commune, nom_commune, cp_commune, email_commune, tel_commune, adresse_commune, contact, reseau_instruction, urbaniste_vra) 
                VALUES (:code_commune, :nom_commune, :cp_commune, :email_commune, :tel_commune, :adresse_commune, :contact, :reseau_instruction, :urbaniste_vra)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':code_commune' => $data['code_commune'],
            ':nom_commune' => $data['nom_commune'],
            ':cp_commune' => $data['cp_commune'],
            ':email_commune' => $data['email_commune'],
            ':tel_commune' => $data['tel_commune'],
            ':adresse_commune' => $data['adresse_commune'],
            ':contact' => $data['contact'],
            ':reseau_instruction' => $data['reseau_instruction'],
            ':urbaniste_vra' => $data['urbaniste_vra']
        ]);

        $logData = [
            'action' => 'Ajout',
            'type' => 'commune',
            'commune' => $data['nom_commune'], 
            'date' => date("c")
        ];

        $this->modificationCollection->insertOne($logData);
    }

    // 📌 Récupérer les données d'une commune précise
    public function getCommuneById($id_commune) {
        $stmt = $this->pdo->prepare("SELECT * FROM communes WHERE id_commune = :id_commune");
        $stmt->execute(['id_commune' => $id_commune]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✏️ Mettre à jour des données d'une commune
    public function updateCommune($data) {
        $sql = "UPDATE communes SET 
                code_commune = :code_commune,
                nom_commune = :nom_commune,
                cp_commune = :cp_commune,
                email_commune = :email_commune,
                tel_commune = :tel_commune,
                adresse_commune = :adresse_commune,
                contact = :contact,
                reseau_instruction = :reseau_instruction,
                urbaniste_vra = :urbaniste_vra
            WHERE id_commune = :id_commune";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_commune' => $data['id_commune'],
            ':code_commune' => $data['code_commune'],
            ':nom_commune' => $data['nom_commune'],
            ':cp_commune' => $data['cp_commune'],
            ':email_commune' => $data['email_commune'],
            ':tel_commune' => $data['tel_commune'],
            ':adresse_commune' => $data['adresse_commune'],
            ':contact' => $data['contact'],
            ':reseau_instruction' => $data['reseau_instruction'],
            ':urbaniste_vra' => $data['urbaniste_vra']
        ]);
    
        $logData = [
            'action' => 'Mise à jour',
            'type' => 'commune',
            'commune' => $data['nom_commune'],
            'date' => date("c")
        ];
    
        $this->modificationCollection->insertOne($logData);
    
        return true; 
    }

    // 🗑️ Supprimer une commune
    public function deleteCommune($id_commune) {
        $stmt = $this->pdo->prepare("SELECT nom_commune FROM communes WHERE id_commune = :id_commune");
        $stmt->execute([':id_commune' => $id_commune]);
        $commune = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($commune) {
            $sql = "DELETE FROM communes WHERE id_commune = :id_commune";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_commune' => $id_commune]);
    
            $logData = [
                'action' => 'Suppression',
                'type' => 'commune',
                'commune' => $commune['nom_commune'],
                'date' => date("c")
            ];
    
            $this->modificationCollection->insertOne($logData);
    
            return true;
        } else {
            return false;
        }
    }
}
?>