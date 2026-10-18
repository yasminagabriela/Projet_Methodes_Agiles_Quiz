<?php
// Connexion à la base de données EN LIGNE
$serveur       = 'sql107.infinityfree.com';
$baseDeDonnees = 'if0_43203796_qivvi';
$utilisateur   = 'if0_43203796';
$motDePasse    = 'W0rkFri3nds';

// Connexion à la base de données LOCALE
// $serveur = 'localhost';
// $baseDeDonnees = 'quizbd';
// $utilisateur = 'root';
// $motDePasse = '';

try {
    $pdo = new PDO("mysql:host=$serveur;dbname=$baseDeDonnees;charset=utf8mb4", $utilisateur, $motDePasse);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    exit('Connexion impossible : ' . $e->getMessage());
}
