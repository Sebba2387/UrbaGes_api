CREATE TABLE plu (
    id_plu INT AUTO_INCREMENT PRIMARY KEY,
    id_commune INT NOT NULL,
    type_plu VARCHAR(255) NOT NULL,
    etat_plu VARCHAR(255) NOT NULL,
    date_plu VARCHAR(255) NOT NULL,
    systeme_ass VARCHAR(255),
    statut_zonage ENUM('traité', 'en cours', 'en attente', 'annulé') NOT NULL,
    statut_pres ENUM('traité', 'en cours', 'en attente', 'annulé') NOT NULL,,
    date_annexion VARCHAR(255),
    lien_zonage VARCHAR(255),
    lien_dhua VARCHAR(255),
    observation_plu TEXT,
    FOREIGN KEY (id_commune) REFERENCES communes(id_commune) ON DELETE CASCADE
);

LOAD DATA INFILE 'D:/plu.csv'
INTO TABLE plu
FIELDS TERMINATED BY ';'
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(id_plu, id_commune, type_plu, etat_plu, date_plu, systeme_ass, statut_zonage, statut_pres, date_annexion, lien_zonage, lien_dhua, observation_plu);