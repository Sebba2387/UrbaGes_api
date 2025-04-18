#### UrbaGes API ####
│── app/                
│   ├── config/               
│   ├── controllers/
│   ├── models/
│── public/
│   ├── api/
│   ├── assets/
│   │   ├── components/
│   │   ├── scss/
│   │   ├── js/
│   │   │   ├── fetch/
│   │   ├── node_modules/
│   │   ├── images/
│   │   ├── pages/
│   ├── router/
│   ├── index.html 
# Installation de l'environnement de développement
- Bootstrap 	✅
- Costumisation de Bootstrap (LiveComplier Sass) 	✅
- Configuration du Docker 	❌
- Configuration de MongoDB 	❌
    + composer init
    + composer require mongodb/mongodb

# Frontend
- Index.html 	✅
- Configuration des routes 	✅
- Construction des pages 	✅
- Pagination 	✅
- Bouton dark 	✅
- Bouton pour vider les champs de rechercher 	✅
- Customisation des tableaux 	✅
- Labels 	✅
- Customisation des codes    ❌
- Validation des champs 	❌
- Contrôle des rôles 	❌

# Backend
- Diagramme MCD 	✅
- Construction de la base de donnée SQL	✅
- Construction de la base de donnée NoSQL	✅
- Configuration des : database, mongodb, config (PDO) 	✅
- Model & Controller : connexion, déconnexion, changement de mot de passe, *inscription* (PDO-SQL-NoSQL) 	✅
- Model & Controller : *filtreUtilisateur*, *updateUtilisater*, *deleteUtilisateur* (PDO-SQL) 	✅
- Model & Controller : filtreCommune, *updateCommune*, *deleteCommune*, *addCommune*  (PDO-SQL) 	✅
- Model & Controller : filtrePlu, *updatePlu* (PDO-SQL) 	✅
- Model & Controller : filtreDossier, updateDossier, deleteDossier, *attributeDossier*, *addDossier* (PDO-SQL-NoSQL) 	✅
- Model & Controller : filtreGep 	✅
- Model & Controller : filtreAttribution 	✅
- Model & Controller : filtreCourrier, updateCourrier, deleteCourrier, *addCourrier*  (PDO-SQL) 	✅

# Full
- Connexion 	✅
- Déconnexion 	✅
- Inscription 	✅
- Profil 	✅
- Recherche d'utilisateur 	✅
- Mise à jour information utilisateur 	✅
- Suppression d'un utilisateur 	✅
- Changement de mot de passe  	✅
- Commune CRUD  	✅
- PLU CRUD  	✅
- GEP Search  	✅
- Dossier CRUD  	✅
- Eviter le rechargement de la page complet (header, sidebar)  	✅
- Changement dynamique de la photo de profil  	✅
- Changement de critère de recherche PLU cp_commune => statut_zonage  	✅
- Liste des communes  	✅


# Instalaltion

- Vérification de version PHP : php -v : PHP 8.2.4
- Vérification MySQL : mysql --version : Ver 15.1 Distrib 10.4.28-MariaDB, for Win64 (AMD64)
- Vérification Apache : httpd --version : Apache2.4
- Vérification MongoDB : mongod --version : db version v8.0.5
- Vérification MongoDB Shell : mongosh --version : 2.4.0
- Vérification MongoCompass : mongodb-compass --version : 1.46.0
- Vérification Composer : composer --version : version 2.8.5
- Vérification Node.js : node --version : v20.6.1
- Vérification Bootstrap : npm list bootstrap : "bootstrap": "^5.3.3", "bootstrap-icons": "^1.11.3"
- Vérification Docker : docker --version : 27.5.1, build 9f9e405