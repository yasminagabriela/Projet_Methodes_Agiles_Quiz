<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        header("Location: index.php");
        exit;
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
                    <h1>Bienvenue !</h1>
                    <p>Connectez-vous pour accéder à Quivvi</p>

                    <form action="login.php" method="post">
                        <label for="role">Vous êtes</label>
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
                        <input type="text" id="nom" name="nom"
                            placeholder="Enter your name" required>

                        <label for="mdp">Mot de passe</label>
                        <input type="password" id="mdp" name="mdp"
                            placeholder="Enter your password" required>

                        <button class="connecter" type="submit">
                            Connexion
                        </button>
                    </form>
                </div>
            </div>

            <div class="image_container">
                <img class="right_image"
                    src="images/background.jpg"
                    alt="Background">
            </div>
        </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
    </body>
</html>
