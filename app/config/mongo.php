<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Charger MongoDB Driver

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$logDB = $mongoClient->urbages_logs;
$logCollection = $logDB->logs;       // Collection pour les logs généraux
$modificationCollection = $logDB->modifications; // Collection pour les logs de modifications
?>