<?php
require_once __DIR__ . '/includes/db.php';

$erreur = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['nom'] ?? '');
    $ancien       = $_POST['ancien'] ?? '';
    $nouveau      = $_POST['nouveau'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($username === '' || $ancien === '') {
        $erreur = "Le nom d'utilisateur et l'ancien mot de passe sont obligatoires.";
    } elseif (strlen($nouveau) < 5) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins 5 caractères.';
    } elseif ($nouveau !== $confirmation) {
        $erreur = 'La confirmation ne correspond pas au nouveau mot de passe.';
    } elseif ($nouveau === $ancien) {
        $erreur = "Le nouveau mot de passe doit être différent de l'ancien.";
    } else {
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Même message si le nom n'existe pas ou si l'ancien mot de passe est faux
        if (!$user || !password_verify($ancien, $user['password'])) {
            $erreur = "Nom d'utilisateur ou ancien mot de passe incorrect.";
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($nouveau, PASSWORD_DEFAULT), $user['id']]);
            $message = 'Votre mot de passe a été modifié. Vous pouvez vous connecter.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quivvi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container">
        <div class="content">
            <div class="login-box">
                <h1>Changer le mot de passe</h1>
                <p>Entrez votre nom d'utilisateur, votre ancien mot de passe et choisissez un nouveau mot de passe.</p>

                <form method="post">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>

                    <label for="ancien">Ancien mot de passe</label>
                    <input type="password" id="ancien" name="ancien" required>

                    <label for="nouveau">Nouveau mot de passe</label>
                    <input type="password" id="nouveau" name="nouveau" minlength="5" required>

                    <label for="confirmation">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirmation" name="confirmation" minlength="5" required>

                    <?php if ($erreur !== ''): ?>
                        <p class="error-text"><?= htmlspecialchars($erreur) ?></p>
                    <?php endif; ?>

                    <?php if ($message !== ''): ?>
                        <p class="success-text"><?= htmlspecialchars($message) ?></p>
                    <?php endif; ?>

                    <button class="connecter" type="submit">Réinitialiser</button>
                    <p><a href="login.php">‹ Retour à la connexion</a></p>
                </form>
            </div>
        </div>

        <div class="image_container">
            <img class="right_image" src="images/background.jpg" alt="Background">
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>

</html>