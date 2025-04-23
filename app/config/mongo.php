<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Charger MongoDB Driver

// Connexion à MongoDB
$mongoClient = new MongoDB\Client("mongodb://mongo:27017");
$logDB = $mongoClient->urbages_logs;

// Définir les collections
$logCollection = $logDB->logs; // Collection pour les logs généraux
$modificationCollection = $logDB->modifications; // Collection pour les logs de modifications

// Passer ces objets à la classe qui en a besoin
return [
    'logCollection' => $logCollection,
    'modificationCollection' => $modificationCollection
];
?>
