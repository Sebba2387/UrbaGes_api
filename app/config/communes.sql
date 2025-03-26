CREATE TABLE communes (
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
);

LOAD DATA INFILE '/path/communes.csv'
INTO TABLE communes
FIELDS TERMINATED BY ';'
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(id_commune, code_commune, nom_commune, cp_commune, email_commune, tel_commune, adresse_commune, contact, reseau_instruction, urbaniste_vra);
