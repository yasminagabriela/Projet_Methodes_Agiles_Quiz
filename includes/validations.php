<?php

require __DIR__ . '/../init.php';
require __DIR__ . '/auth.php';

// Direct without submitting the form since the form is submitted via POST method to this file with action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['nom'] ?? '');
$password = $_POST['mdp'] ?? '';
$role      = $_POST['role'] ?? 'etudiant';

if ($role !== 'etudiant' && $role !== 'enseignant') {
    header('Location: ../login.php?error=invalid_credentials');
    exit;
}

if ($username === '' || $password === '') {
    header('Location: ../login.php?error=invalid_credentials');
    exit;
}

if (attempt_login($pdo, $username, $password, $role)) {
    header('Location: ../index.php');
    exit;
}

header('Location: ../login.php?error=invalid_credentials&role=' . $role);
exit;
