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

    public function searchCommunes($code_commune, $nom_commune, $cp_commune) {
        $query = "SELECT * FROM communes WHERE 1=1";
        $params = [];
    
        if (!empty($code_commune)) {
            $query .= " AND code_commune LIKE :code_commune";
            $params['code_commune'] = "%$code_commune%";
        }
        if (!empty($nom_commune)) {
            $query .= " AND nom_commune LIKE :nom_commune";
            $params['nom_commune'] = "%$nom_commune%";
        }
        if (!empty($cp_commune)) {
            $query .= " AND cp_commune LIKE :cp_commune";
            $params['cp_commune'] = "%$cp_commune%";
        }
    
        echo "SQL : " . $query . "\n";
        echo "Params : " . json_encode($params) . "\n";
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}
?>