<?php
// Initialisation de la base de données quizBD
require 'includes/db.php';
 
$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)');
 
$pdo->exec("INSERT IGNORE INTO users (username, password)
VALUES ('Farid', 'Farid')");
 
echo 'Base quizDB initialisée !';