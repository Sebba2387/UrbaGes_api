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
- Model & Controller : filtreDossier, updateDossier, deleteDossier, *attributeDossier*, *addDossier* (PDO-SQL-NoSQL) 	❌
- Model & Controller : filtreCourrier, updateCourrier, deleteCourrier, *addCourrier*  (PDO-SQL) 	❌

