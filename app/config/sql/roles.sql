-- Création de la table roles
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role ENUM('admin', 'moderateur', 'utilisateur') NOT NULL UNIQUE
);

-- Insérer les rôles par défaut
INSERT INTO roles (nom_role) VALUES 
('admin'),
('moderateur'),
('utilisateur');

-- Ajout de la colonne id_role dans utilisateurs avec une clé étrangère
ALTER TABLE utilisateurs ADD COLUMN id_role INT DEFAULT 3; -- Par défaut, utilisateur simple
ALTER TABLE utilisateurs ADD CONSTRAINT fk_role FOREIGN KEY (id_role) REFERENCES roles(id_role);


