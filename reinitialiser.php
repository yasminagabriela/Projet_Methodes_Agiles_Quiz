<?php
require_once __DIR__ . '/includes/db.php';

$erreur = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['nom'] ?? '');
    $nouveau      = $_POST['nouveau'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($username === '') {
        $erreur = "Le nom d'utilisateur est obligatoire.";
    } elseif (strlen($nouveau) < 5) {
        $erreur = 'Le mot de passe doit contenir au moins 5 caractères.';
    } elseif ($nouveau !== $confirmation) {
        $erreur = 'La confirmation ne correspond pas au mot de passe.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
        $stmt->execute([password_hash($nouveau, PASSWORD_DEFAULT), $username]);

        // Même message que le nom existe ou non
        $message = "Si ce nom d'utilisateur existe, son mot de passe a été modifié. Vous pouvez vous connecter.";
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
                <h1>Mot de passe oublié</h1>
                <p>Entrez votre nom d'utilisateur et choisissez un nouveau mot de passe.</p>

                <form method="post">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>

                    <label for="nouveau">Nouveau mot de passe</label>
                    <input type="password" id="nouveau" name="nouveau" minlength="4" required>

                    <label for="confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="confirmation" name="confirmation" minlength="4" required>

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