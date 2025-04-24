-- Initialisation de la base urbages_db
-- Encodage UTF8
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ROLES
CREATE TABLE IF NOT EXISTS roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role ENUM('admin', 'moderateur', 'utilisateur') NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- UTILISATEURS
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    annee_naissance VARCHAR(255) NOT NULL,
    pseudo VARCHAR(50) NOT NULL,
    genre ENUM('homme', 'femme') NOT NULL,
    poste VARCHAR(50) NOT NULL,
    id_role INT DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des rôles de test
INSERT INTO roles (nom_role) VALUES 
('admin'),
('moderateur'),
('utilisateur');

-- Contrainte clé étrangère après création
ALTER TABLE utilisateurs 
ADD CONSTRAINT fk_role FOREIGN KEY (id_role) REFERENCES roles(id_role);

-- COMMUNES
CREATE TABLE IF NOT EXISTS communes (
    id_commune INT PRIMARY KEY AUTO_INCREMENT,
    code_commune INT NOT NULL,
    nom_commune VARCHAR(100) NOT NULL,
    cp_commune INT NOT NULL,
    email_commune VARCHAR(100),
    tel_commune VARCHAR(50),
    adresse_commune VARCHAR(254),
    contact VARCHAR(50),
    reseau_instruction VARCHAR(50),
    urbaniste_vra VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DOSSIERS
CREATE TABLE IF NOT EXISTS dossiers (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSTRUCTIONS
CREATE TABLE IF NOT EXISTS instructions (
    id_instruction INT AUTO_INCREMENT PRIMARY KEY,
    id_commune INT,
    id_utilisateur INT,
    numero_dossier VARCHAR(255),
    libelle VARCHAR(255),
    date_demande VARCHAR(255),
    date_limite VARCHAR(255),
    FOREIGN KEY (id_commune) REFERENCES communes(id_commune),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PLU
CREATE TABLE IF NOT EXISTS plu (
    id_plu INT AUTO_INCREMENT PRIMARY KEY,
    id_commune INT NOT NULL,
    type_plu VARCHAR(255) NOT NULL,
    etat_plu VARCHAR(255) NOT NULL,
    date_plu VARCHAR(255) NOT NULL,
    systeme_ass VARCHAR(255),
    statut_zonage ENUM('traité', 'en cours', 'en attente', 'annulé') NOT NULL,
    statut_pres ENUM('traité', 'en cours', 'en attente', 'annulé') NOT NULL,
    date_annexion VARCHAR(255),
    lien_zonage VARCHAR(255),
    lien_dhua VARCHAR(255),
    observation_plu TEXT,
    FOREIGN KEY (id_commune) REFERENCES communes(id_commune) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GEP
CREATE TABLE IF NOT EXISTS gep (
    id_gep INT AUTO_INCREMENT PRIMARY KEY,
    code_commune INT NOT NULL,
    nom_commune VARCHAR(255) NOT NULL,
    cadastre VARCHAR(100),
    section VARCHAR(100),
    numero INT,
    surface FLOAT,
    captage VARCHAR(255),
    captage_regles TEXT,
    captage_pct INT,
    dys_pct INT,
    sage_indice INT,
    sage_pct INT,
    montana_zone INT,
    plu_sous_zone VARCHAR(100),
    plu_regle TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- COURRIERS
CREATE TABLE IF NOT EXISTS courriers (
    id_courrier INT AUTO_INCREMENT PRIMARY KEY,
    type_courrier ENUM('Instruction complexe', 'Projet', 'Accompagnement', 'Servitude', 'Rétrocession', 'Autre') NOT NULL,
    libelle_courrier VARCHAR(255) NOT NULL,
    corps_courrier TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERTS DE TEST (vides) pour valider les insertions
INSERT INTO utilisateurs (nom, prenom, email, password, annee_naissance, pseudo, poste, genre, id_role
) VALUES (
    'Test', 'User', 'test@example.com', 
    '$2y$10$dGrloZa1Rf0fIWcEeglcvuOfVNO5IhpglzeG0JK/DKSlqsMXbDjHm', '1990-01-01', 'testuser', 'Responsable', 'homme', 1);

INSERT INTO communes (code_commune, nom_commune, cp_commune, email_commune, tel_commune, adresse_commune, contact, reseau_instruction, urbaniste_vra)
VALUES (12345, 'Paris', 75000, 'contact@paris.fr', '01 23 45 67 89', '12 rue de Paris', 'Monsieur Dupont', 'Réseau 1', 'Jean-Pierre Martin');

INSERT INTO dossiers (
    id_commune, id_utilisateur, numero_dossier, id_cadastre, libelle, type_dossier, sous_type_dossier, date_demande, date_limite, statut, 
    lien_calypso, lien_dossier, observation
    ) VALUES (
    1, 1, 'D12345', 'C123', 'Dossier de projet de branchement', 
    'Projet', 'Branchement', '2025-04-01', '2025-05-01', 'en cours', 
    'https://calypso.exemple.com/dossier/D12345', 'https://dossier.exemple.com/D12345', 'Aucune observation');

INSERT INTO instructions (
    id_commune, id_utilisateur, numero_dossier, libelle, date_demande, date_limite
    ) VALUES (
    1, 1, 'INST-2025-001', 'Instruction pour projet extension', 
    '2025-04-10', '2025-05-10');

INSERT INTO plu (
    id_commune, type_plu, etat_plu, date_plu, systeme_ass,
    statut_zonage, statut_pres, date_annexion, lien_zonage,
    lien_dhua, observation_plu
    ) VALUES (
    1, 'PLU approuvé', 'en vigueur', '2023-06-15', 'Tout à égout',
    'en cours', 'en attente', '2023-07-01', 
    'https://example.com/zonage.pdf',
    'https://example.com/dhua.pdf',
    'Zonage en révision pour le secteur nord.');

INSERT INTO gep (
    code_commune, nom_commune, cadastre, section, numero,
    surface, captage, captage_regles, captage_pct,
    dys_pct, sage_indice, sage_pct, montana_zone,
    plu_sous_zone, plu_regle)
VALUES (
    12345, 'Commune de Test', 'AB123', 'AB', 123,
    1542.75, 'Captage Nord', 'Règlement captage en vigueur', 80,
    10, 5, 60, 1,
    'Zone Uc', 'Règles de la zone Uc concernant les hauteurs et emprises');

INSERT INTO courriers (
    type_courrier, libelle_courrier, corps_courrier
) VALUES (
    'Projet',
    'Demande de complément',
    'Madame, Monsieur,<br><br>Nous vous prions de bien vouloir nous faire parvenir les pièces complémentaires nécessaires à votre dossier. Sans ces éléments, Le diagnostic ne pourra être poursuivie.<br><br>Veuillez agréer, Madame, Monsieur, nos salutations distinguées.'
);
