<?php
// Initialisation de la base de données
require 'includes/db.php';

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'etudiant'
)");

// Ajouter la colonne 'role' à la table (À SUPPRIMER APRÈS QUE TOUT LE MONDE L'AIT FAIT)
$colonne = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
if (!$colonne) {
    $pdo->exec("ALTER TABLE users ADD role VARCHAR(20) NOT NULL DEFAULT 'etudiant'");
    $pdo->exec("UPDATE users SET role = 'enseignant' WHERE username = 'Farid'");
}

$hash = password_hash('Farid', PASSWORD_DEFAULT);
$pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES ('Farid', ?, 'enseignant')")->execute([$hash]);
?>
