<?php
// Connexion à la base de données LOCALE
$serveur       = 'localhost';
$baseDeDonnees = 'quizbd';
$utilisateur   = 'root';
$motDePasse    = '';

if (file_exists(__DIR__ . '/db_online.php')) {
    require __DIR__ . '/db_online.php';
}

try {
    $pdo = new PDO("mysql:host=$serveur;dbname=$baseDeDonnees;charset=utf8mb4", $utilisateur, $motDePasse);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    exit('Connexion impossible : ' . $e->getMessage());
}
