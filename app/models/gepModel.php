<?php
require_once __DIR__ . '/../config/database.php';

class GepModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getNomCommunes() {
        $sql = "SELECT DISTINCT nom_commune FROM gep ORDER BY nom_commune ASC";
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            throw new Exception("Erreur lors de la récupération des communes");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchGep($nom_commune, $section, $numero) {
        $query = "SELECT * FROM gep WHERE 1=1";
        $params = [];
    
        if (!empty($nom_commune)) {
            $query .= " AND nom_commune LIKE :nom_commune";
            $params['nom_commune'] = "%$nom_commune%";
        }
        if (!empty($section)) {
            $query .= " AND section LIKE :section";
            $params['section'] = "%$section%";
        }
        if (!empty($numero)) {
            $query .= " AND numero LIKE :numero";
            $params['numero'] = "%$numero%";
        }
    
        echo "SQL : " . $query . "\n";
        echo "Params : " . json_encode($params) . "\n";
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
