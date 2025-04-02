CREATE TABLE DOSSIERS (
    id_dossier INT AUTO_INCREMENT PRIMARY KEY,
    id_commune INT NOT NULL,
    id_utilisateur INT NOT NULL,
    numero_dossier VARCHAR(255),
    id_cadastre VARCHAR(255),
    libelle TEXT,
    type_dossier ENUM('Instruction complexe', 'Projet', 'Accompagnement', 'Servitude', 'Rétrocession') NOT NULL,
    sous_type_dossier ENUM('Direction', 'Extension', 'Branchement', 'Opération', 'SPANC', 'EUND', 'SPGEP', 'Autre') NOT NULL,
    date_demande VARCHAR(255),
    date_limite VARCHAR(255),
    statut ENUM('traité', 'en cours', 'en attente', 'annulé') NOT NULL,
    lien_calypso VARCHAR(255),
    lien_dossier VARCHAR(255),
    observation TEXT,
    FOREIGN KEY (id_commune) REFERENCES communes(id_commune) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);
