<?php
//pour envoyer vers l'accueil quelqun qui est déja connecté mais qui est allé sur le login.php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    header('Location: index.php');
    exit;
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

    <div class="container">
        <div class="content">

            <div class="login-box">
                <h1>Bienvenue !</h1>
                <p>Connectez-vous pour accéder à Quivvi</p>

                <form action="includes/validations.php" method="post">

                    <label>Vous êtes</label>

                    <div class="role-selection">

                        <label class="role-option selected" id="etudiant-label">
                            <input type="radio" name="role" value="etudiant" checked>
                            Étudiant
                        </label>

                        <label class="role-option" id="enseignant-label">
                            <input type="radio" name="role" value="enseignant">
                            Enseignant
                        </label>

                    </div>

                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" maxlength="20" required>

                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp" name="mdp" placeholder="Entrez votre mot de passe" required>

                    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
                        <p class="error-text">
                            Votre nom d'utilisateur ou mot de passe n'est pas valide.
                        </p>
                    <?php endif; ?>
                    <p><a href="reinitialiser.php">Changer de mot de passe ?</a></p>

                    <button class="connecter" type="submit">
                        Connexion
                    </button>

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