<?php
// Initialiser la base de données
require 'includes/db.php';


// Créer la table 'users'
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'enseignant') NOT NULL DEFAULT 'etudiant'
)");

// Créer la table 'quiz'
$pdo->exec("CREATE TABLE IF NOT EXISTS quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Créer la table 'questions'
$pdo->exec("CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enonce TEXT NOT NULL,
    type ENUM('choix_multiple', 'vrai_faux') NOT NULL,
    quiz_id INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
    ON DELETE CASCADE
)");

// Créer la table 'reponses'
$pdo->exec("CREATE TABLE IF NOT EXISTS reponses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texte VARCHAR(255) NOT NULL,
    est_correcte BOOL NOT NULL DEFAULT FALSE,
    question_id INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id)
    ON DELETE CASCADE
)");
