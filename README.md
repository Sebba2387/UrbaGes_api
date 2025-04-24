# Manuel de déploiement de l'application Urbages (Déploiement local avec Docker)

Projet PHP MVC utilisant MySQL et MongoDB, conçu pour être lancé localement en quelques commandes via Docker.

## Structure du projet

/UrbaGes
│── app/	# Code métier de l'application (architecture MVC)                
│   ├── config/		# Fichiers de configuration (base de données, MongoDB, etc.)          
│   ├── controllers/	# Contrôleurs : logique de traitement des requêtes
│   ├── models/	# Modèles : interaction avec les bases de données
│── logs/	# Fichiers de logs (journalisation, erreurs, etc.)
│── public/	# Répertoire destiné à être accessible par public
│   ├── api/	# API REST côté serveur
│   ├── assets/	# Ressources statiques
│   │   ├── scss/	# Feuilles de style SCSS
│   │   ├── js/	# Scripts JavaScript
│   │   │   ├── fetch/	# Requêtes fetch() pour l'API 
│   │   ├── images/	# Images utilisées dans l'interface
│   ├── pages/	# Pages HTML accessibles depuis le routeur JS
│   ├── router/	# Gestion du routage côté client (JavaScript)
│   ├── components/	# Composants réutilisables HTML/JS 
│   ├── node_modules/ # Bibliothèque Bootstrap et Bootstrap icons
│── ressources/	 # Fichiers annexes
│── vendor/	# Dépendances PHP installées via Composer 
│   ├── index.html
├── apache/
│	└── 000-default.conf # Configuration Apache personnalisée
├── sql/ 
│	└── init.sql # Script de création de la base MySQL et données de test
├── docker-compose.yml # Orchestration des services
├── Dockerfile # Configuration PHP + Apache + MongoDB

## Prérequis

- Installation du [Docker] : https://www.docker.com
- Installation du [Docker Compose] : https://docs.docker.com/compose

## Lancement des services :

À partir d'un terminal, se placer à la racine du projet et exécuter : 
```bash
docker-compose up --build
```

### Accés aux services :

- Site web : http://localhost:8080
- phpMyAdmin (MySQL) : http://localhost:8081

### Identifiants phpMyAdmin (MySQL) :

- Serveur : mysql
- Utilisateur : user
- Mot de passe : pass

### Base de données

#### MySQL

- Nom de la base de données MySQL : urbages_db : Création automatique via 'sql/init.sql', ainsi que les tables suivantes :
	* utilisateurs
	* roles
	* dossiers
	* instructions
	* communes
	* plu
	* gep
	* courriers

#### MongoDB
- Nom de la base de données NoSQL : urbages_logs : Création automatique lors du premier insert.
	* Collections : logs, modifications
	* Connexion via "mongo.php"

### Utilisateurs de test

- **Email** : test@example.com
- **Mot de passe** : password

## Technologie utilisées :

- PHP 8.2 + Apache
- MySQL (MariaDB 10.4)
- MongoDB 8.0
- phpMyAdmin
- PDO pour MySQL
- MongoDB Client PHP (via Composer)

## Arrêt les services

À partir d'un terminal, se placer à la racine du projet et exécuter : 
```bash
docker-compose down # Arrêter les services
docker-compose down -v # Arrêter les services et vider les volumes

```

## Contact

Ce projet a été réalisé dans le cadre de la formation Graduate Flutter.
Pour toute question, suggestion ou contribution, n'hésitez pas à nous contacter directement via ce dépôt GitHub.
