<?php
// Connexion à la base DexBD
$dsn = 'mysql:host=localhost;dbname=QuizBD;charset=utf8mb4';
$utilisateur = 'root';
$motDePasse = '';
 
try {
    $pdo = new PDO($dsn, $utilisateur, $motDePasse);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('Connexion impossible : ' . $e->getMessage());
}