<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongo.php';
class CommuneModel {
    private $pdo;
    private $modificationCollection;

    public function __construct($pdo, $modificationCollection) {
        $this->pdo = $pdo;
        $this->modificationCollection = $modificationCollection;
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
    
}
?>