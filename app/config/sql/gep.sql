CREATE TABLE gep (
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


LOAD DATA INFILE 'D:/gep.csv'
INTO TABLE gep
FIELDS TERMINATED BY ';'
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(id_gep, code_commune, nom_commune, cadastre, section, numero, surface, captage, captage_regles, captage_pct, 
dys_pct, sage_indice, sage_pct, montana_zone, plu_sous_zone, plu_regle);