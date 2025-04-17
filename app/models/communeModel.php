<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';
class CommuneModel {
    private $pdo;
    private $modificationCollection;

    // Constructeur qui prend en charge la connexion PDO et la collection MongoDB
    public function __construct($pdo, $mongoConfig) {
        $this->pdo = $pdo;
        // Initialisation de la collection MongoDB pour les logs de modification
        $this->modificationCollection = $mongoConfig; 
    }

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

    // Fonction pour récupérer le nom_commune
    public function getAllCommunes() {
        // Prépare la requête pour récupérer toutes les communes
        $sql = "SELECT id_commune, nom_commune FROM communes";
        $stmt = $this->pdo->query($sql);
        $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        return $communes;
    }

    // Fonction pour ajouter une commune
    public function addCommune($data) {
        // Préparer et exécuter l'insertion dans la base SQL
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

        // Enregistrement dans MongoDB
        $logData = [
            'action' => 'Ajout',
            'type' => 'commune',
            'commune' => $data['nom_commune'], // Détails sur la commune ajoutée
            'date' => date("c")
        ];

        // Insérer un log dans la collection MongoDB pour les modifications
        $this->modificationCollection->insertOne($logData);
    }

    // Fonction de récupération d'une commune
    public function getCommuneById($id_commune) {
        $stmt = $this->pdo->prepare("SELECT * FROM communes WHERE id_commune = :id_commune");
        $stmt->execute(['id_commune' => $id_commune]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fonction de mise à jour d'une commune
    public function updateCommune($data) {
        // Préparer et exécuter la mise à jour dans la base SQL
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
    
        // Enregistrement dans MongoDB
        $logData = [
            'action' => 'Mise à jour',
            'type' => 'commune',
            'commune' => $data['nom_commune'], // Détails sur la commune mise à jour
            'date' => date("c")
        ];
    
        // Insérer un log dans la collection MongoDB pour les modifications
        $this->modificationCollection->insertOne($logData);
    
        return true;  // Retourner true si la mise à jour réussit
    }

    // Fonction de suppression d'une commune
    public function deleteCommune($id_commune) {
        // Récupérer le nom de la commune avant de la supprimer
        $stmt = $this->pdo->prepare("SELECT nom_commune FROM communes WHERE id_commune = :id_commune");
        $stmt->execute([':id_commune' => $id_commune]);
        $commune = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Si la commune existe, procéder à la suppression
        if ($commune) {
            // Préparer et exécuter la suppression dans la base SQL
            $sql = "DELETE FROM communes WHERE id_commune = :id_commune";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_commune' => $id_commune]);
    
            // Enregistrement dans MongoDB
            $logData = [
                'action' => 'Suppression',
                'type' => 'commune',
                // 'id_commune' => $id_commune, // ID de la commune supprimée
                'commune' => $commune['nom_commune'], // Nom de la commune supprimée
                'date' => date("c")
            ];
    
            // Insérer un log dans la collection MongoDB pour la suppression
            $this->modificationCollection->insertOne($logData);
    
            return true;  // Retourner true si la suppression réussit
        } else {
            // Si la commune n'existe pas, on peut retourner false ou gérer l'erreur
            return false; // Commune non trouvée
        }
    }
}
?>