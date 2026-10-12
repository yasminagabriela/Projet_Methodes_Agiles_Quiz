<?php
// Gestion de l'authentification et de la session

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,               // le cookie disparaît à la fermeture du navigateur
        'path'     => '/',
        'httponly' => true,            
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function attempt_login(PDO $pdo, string $username, string $password): bool {
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    $ok = false;
    if (password_verify($password, $user['password'])) {
        $ok = true;
    } elseif (hash_equals($user['password'], $password)) {
        // Ancien mot de passe en clair : on l'accepte, puis on le convertit en hash
        $ok = true;
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $upd->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    if ($ok) {
        session_regenerate_id(true);   // évite la fixation de session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
    return $ok;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}