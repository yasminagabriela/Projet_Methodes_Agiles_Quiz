<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quivvi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="login-box">
                <h1>Bienvenue !</h1>
                <p>Connectez-vous pour accéder à Quivvi</p>

                <form action="" method="post">
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
</body>

<!-- Switch dark/light mode -->
<button class="theme-toggle" id="theme-toggle" type="button">☾</button>
<script>
    const boutonTheme = document.getElementById("theme-toggle");

    boutonTheme.addEventListener("click", function() {
        document.body.classList.toggle("dark-mode");
        if (document.body.classList.contains("dark-mode")) {
            boutonTheme.textContent = "☀";
        } else {
            boutonTheme.textContent = "☾";
        }
    });
</script>

</body>
</html>
