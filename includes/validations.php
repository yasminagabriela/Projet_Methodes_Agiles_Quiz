<?php

require __DIR__ . '/../init.php';
require __DIR__ . '/auth.php';

// Direct without submitting the form since the form is submitted via POST method to this file with action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = $_POST['nom'] ?? '';
$password = $_POST['mdp'] ?? '';

$requete = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$requete->execute([$username]);
$user = $requete->fetch();

// === -> case-sensitive
if ($user && $username === 'Farid' && $password === $user['password']) {
    header('Location: ../index.php');
    exit;
}

header('Location: ../login.php?error=invalid_credentials');
exit;

?>