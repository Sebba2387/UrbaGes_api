<?php
require_once __DIR__ . '/../config/database.php';

class DossierModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
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
        $sql = "SELECT d.id_dossier, u.pseudo, c.nom_commune, d.numero_dossier, d.type_dossier, d.sous_type_dossier, d.id_cadastre, d.libelle, d.date_demande, d.date_limite, d.statut, d.lien_calypso 
                FROM dossiers d
                JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                JOIN communes c ON d.id_commune = c.id_commune 
                WHERE d.id_dossier = :id_dossier";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_dossier', $id_dossier, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
