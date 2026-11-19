<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';
$erreur = '';
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien       = $_POST['ancien'] ?? '';
    $nouveau      = $_POST['nouveau'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($ancien, $hash)) {
        $erreur = "L'ancien mot de passe est incorrect.";
    } elseif (mb_strlen($nouveau) < 5) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins 5 caractères.';
    } elseif ($nouveau !== $confirmation) {
        $erreur = 'La confirmation ne correspond pas au nouveau mot de passe.';
    } elseif ($nouveau === $ancien) {
        $erreur = "Le nouveau mot de passe doit être différent de l'ancien.";
    } else {
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($nouveau, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        session_regenerate_id(true);
        $succes = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qivvi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="dashboard-user">
        <span class="current-user"><?= $titreRole; ?></span>
        <button type="button" class="deconnecter" onclick="window.location.href='logout.php'">Se déconnecter</button>
    </div>

    <main class="dashboard-content">
        <div class="login-box">
            <a href="index.php" class="return">‹ Retour</a>
            <h1>Changer le mot de passe</h1>

            <form method="post">
                <label for="ancien">Ancien mot de passe</label>
                <input type="password" id="ancien" name="ancien" required>

                <label for="nouveau">Nouveau mot de passe</label>
                <input type="password" id="nouveau" name="nouveau" minlength="5" required>

                <label for="confirmation">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirmation" name="confirmation" minlength="5" required>

                <?php if ($erreur !== ''): ?>
                    <p class="error-text"><?= htmlspecialchars($erreur) ?></p>
                <?php endif; ?>

                <?php if ($succes): ?>
                    <p class="success-text">Votre mot de passe a été modifié.</p>
                <?php endif; ?>

                <button class="connecter" type="submit">Enregistrer</button>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>

</html>