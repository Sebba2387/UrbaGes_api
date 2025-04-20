CREATE TABLE INSTRUCTIONS (
    id_instruction INT AUTO_INCREMENT PRIMARY KEY,
    id_commune INT,
    id_utilisateur INT,
    numero_dossier VARCHAR(255),
    libelle VARCHAR(255),
    date_demande DATE,
    date_limite DATE,
    FOREIGN KEY (id_commune) REFERENCES COMMUNES(id_commune),
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEURS(id_utilisateur)
);
