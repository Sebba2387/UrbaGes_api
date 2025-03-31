<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';
class PluModel {
    private $pdo;
    private $modificationCollection;

    // Constructeur qui prend en charge la connexion PDO et la collection MongoDB
    public function __construct($pdo, $mongoConfig) {
        $this->pdo = $pdo;
        // Initialisation de la collection MongoDB pour les logs de modification
        $this->modificationCollection = $mongoConfig; 
    }

    public function searchPlu($code_commune, $nom_commune, $cp_commune, $etat_plu) {
        $query = "SELECT plu.*, communes.code_commune, communes.nom_commune, communes.cp_commune 
                  FROM plu 
                  JOIN communes ON plu.id_commune = communes.id_commune
                  WHERE 1=1";
        $params = [];
    
        if (!empty($code_commune)) {
            $query .= " AND communes.code_commune LIKE :code_commune";
            $params['code_commune'] = "%$code_commune%";
        }
        if (!empty($nom_commune)) {
            $query .= " AND communes.nom_commune LIKE :nom_commune";
            $params['nom_commune'] = "%$nom_commune%";
        }
        if (!empty($cp_commune)) {
            $query .= " AND communes.cp_commune LIKE :cp_commune";
            $params['cp_commune'] = "%$cp_commune%";
        }
        if (!empty($etat_plu)) {
            $query .= " AND plu.etat_plu LIKE :etat_plu";
            $params['etat_plu'] = "%$etat_plu%";
        }
    
        echo "SQL : " . $query . "\n";
        echo "Params : " . json_encode($params) . "\n";
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un PLU par son ID
    public function getPluById($id_plu) {
        $query = "SELECT * FROM plu WHERE id_plu = :id_plu";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id_plu", $id_plu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Modifier un PLU
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

        // Enregistrement dans MongoDB
        $logData = [
            'action' => 'Mise à jour des données',
            'type' => 'PLU',
            'commune' => $data['id_plu'], // Détails sur la commune mise à jour
            'date' => date("c")
        ];
    
        // Insérer un log dans la collection MongoDB pour les modifications
        $this->modificationCollection->insertOne($logData);
    
        return true;  // Retourner true si la mise à jour réussit
    }

}
?>