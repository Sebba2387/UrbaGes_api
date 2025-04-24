<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';
class PluModel {
    private $pdo;
    private $modificationCollection;

    public function __construct($pdo, $mongoConfig) {
        $this->pdo = $pdo;
        $this->modificationCollection = $mongoConfig; 
    }

    // 🔍 Rechercher un PLU
    public function searchPlu($id_commune, $statut_zonage, $statut_pres, $etat_plu) {
        $query = "SELECT plu.*, communes.code_commune, communes.nom_commune, communes.cp_commune 
                  FROM plu 
                  JOIN communes ON plu.id_commune = communes.id_commune
                  WHERE 1=1";
        $params = [];
    
        if (!empty($id_commune)) {
            $query .= " AND plu.id_commune = :id_commune";
            $params['id_commune'] = $id_commune;
        }
        if (!empty($statut_zonage)) {
            $query .= " AND plu.statut_zonage = :statut_zonage";
            $params['statut_zonage'] = $statut_zonage;
        }
        if (!empty($statut_pres)) {
            $query .= " AND plu.statut_pres = :statut_pres";
            $params['statut_pres'] = $statut_pres;
        }
        if (!empty($etat_plu)) {
            $query .= " AND plu.etat_plu LIKE :etat_plu";
            $params['etat_plu'] = "%$etat_plu%";
        }
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 Récupérer les noms de toutes les communes
    public function getAllCommunes() {
        // Prépare la requête pour récupérer toutes les communes
        $sql = "SELECT id_commune, nom_commune FROM communes";
        $stmt = $this->pdo->query($sql);
        $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        return $communes;
    }

    // 📌 Récupérer les données d'un PLU
    public function getPluById($id_plu) {
        $query = "SELECT * FROM plu WHERE id_plu = :id_plu";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id_plu", $id_plu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✏️ Mettre à jour des données d'un PLU
    public function updatePlu($data) {
        $sql = "UPDATE plu SET 
                    type_plu = :type_plu,
                    etat_plu = :etat_plu,
                    date_plu = :date_plu,
                    systeme_ass = :systeme_ass,
                    statut_zonage = :statut_zonage,
                    statut_pres = :statut_pres,
                    date_annexion = :date_annexion,
                    lien_zonage = :lien_zonage,
                    lien_dhua = :lien_dhua,
                    observation_plu = :observation_plu
                WHERE id_plu = :id_plu";
    
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':type_plu' => $data['type_plu'],
            ':etat_plu' => $data['etat_plu'],
            ':date_plu' => $data['date_plu'],
            ':systeme_ass' => $data['systeme_ass'],
            ':statut_zonage' => $data['statut_zonage'],
            ':statut_pres' => $data['statut_pres'],
            ':date_annexion' => $data['date_annexion'],
            ':lien_zonage' => $data['lien_zonage'],
            ':lien_dhua' => $data['lien_dhua'],
            ':observation_plu' => $data['observation_plu'],
            ':id_plu' => $data['id_plu']
        ]);

        $logData = [
            'action' => 'Mise à jour des données de PLU',
            'Dossier' => $data['id_plu'],
            'type' => $data['type_plu'],
            'mission' => $data['statut_zonage'],
            'date' => date("c")
        ];
    
        $this->modificationCollection->insertOne($logData);
    
        return true;
    }

}
?>