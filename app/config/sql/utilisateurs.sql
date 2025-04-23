CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    annee_naissance DATE NOT NULL,
    pseudo VARCHAR(50) NOT NULL,
    poste VARCHAR(50) NOT NULL,
    genre ENUM('homme', 'femme') NOT NULL
);