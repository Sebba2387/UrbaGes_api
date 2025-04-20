CREATE TABLE courriers (
    id_courrier INT AUTO_INCREMENT PRIMARY KEY,
    type_courrier ENUM('Instruction complexe', 'Projet', 'Accompagnement', 'Servitude', 'Rétrocession', 'Autre') NOT NULL,
    libelle_courrier VARCHAR(255) NOT NULL,
    corps_courrier TEXT NOT NULL
);
