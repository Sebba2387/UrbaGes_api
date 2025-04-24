<?php
class StatsModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ⚙️ Récupérer les statistiques
    public function getStats() {
        $currentYear = date("Y");
        $previousYears = range($currentYear - 5, $currentYear);

        // Total des instructions
        $query = "SELECT COUNT(*) AS totalInstructions FROM instructions WHERE YEAR(date_demande) = :currentYear";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['currentYear' => $currentYear]);
        $totalInstructions = $stmt->fetchColumn();

        // Statistiques de 'Instruction complexe' avec 'traité' et 'en cours'
        $query = "SELECT
                    COUNT(CASE WHEN type_dossier = 'Instruction complexe' AND statut = 'traité' THEN 1 END) AS complexTraites,
                    COUNT(CASE WHEN type_dossier = 'Instruction complexe' AND statut = 'en cours' THEN 1 END) AS complexEnCours,
                    COUNT(CASE WHEN sous_type_dossier = 'SPGEP' AND statut = 'en cours' THEN 1 END) AS spgepEnCours,
                    COUNT(CASE WHEN type_dossier = 'Servitude' AND statut = 'en cours' THEN 1 END) AS servitudeEnCours,
                    COUNT(CASE WHEN type_dossier = 'Rétrocession' AND statut = 'en cours' THEN 1 END) AS retrocessionEnCours
                    FROM dossiers";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Statistiques des instructions sur les 5 dernières années
        $instructionsGraph = [];
        foreach ($previousYears as $year) {
            $query = "SELECT COUNT(*) AS total FROM instructions WHERE YEAR(date_demande) = :year";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['year' => $year]);
            $instructionsGraph[] = ['year' => $year, 'total' => $stmt->fetchColumn()];
        }

        // Statistiques des instructions par intervenant
        $query = "SELECT u.pseudo, COUNT(*) AS total FROM instructions i JOIN utilisateurs u ON i.id_utilisateur = u.id_utilisateur GROUP BY u.pseudo";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $instructionsParIntervenant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Statistiques Servitude sur les 5 dernières années
        $servitudeGraph = [];
        foreach ($previousYears as $year) {
            $query = "SELECT COUNT(*) AS total FROM dossiers WHERE type_dossier = 'Servitude' AND YEAR(date_demande) = :year";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['year' => $year]);
            $servitudeGraph[] = ['year' => $year, 'total' => $stmt->fetchColumn()];
        }

        // Statistiques Servitude par statut 
        $query = "SELECT statut, COUNT(*) AS total FROM dossiers WHERE type_dossier = 'Servitude' GROUP BY statut";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $servitudeParStatut = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Statistiques Rétrocession sur les 5 dernières années
        $retrocessionGraph = [];
        foreach ($previousYears as $year) {
            $query = "SELECT COUNT(*) AS total FROM dossiers WHERE type_dossier = 'Rétrocession' AND YEAR(date_demande) = :year";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['year' => $year]);
            $retrocessionGraph[] = ['year' => $year, 'total' => $stmt->fetchColumn()];
        }
        
        // Statistiques Rétrocession par statut
        $query = "SELECT statut, COUNT(*) AS total FROM dossiers WHERE type_dossier = 'Rétrocession' GROUP BY statut";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $retrocessionParStatut = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totalInstructions' => $totalInstructions,
            'complexTraites' => $stats['complexTraites'],
            'complexEnCours' => $stats['complexEnCours'],
            'spgepEnCours' => $stats['spgepEnCours'],
            'servitudeEnCours' => $stats['servitudeEnCours'],
            'retrocessionEnCours' => $stats['retrocessionEnCours'],
            'instructionsGraph' => $instructionsGraph,
            'instructionsParIntervenant' => $instructionsParIntervenant,
            'servitudeGraph' => $servitudeGraph,
            'servitudeStatusGraph' => $servitudeParStatut,
            'retrocessionGraph' => $retrocessionGraph,
            'retrocessionStatusGraph' => $retrocessionParStatut
        ];
    }
}
?>
