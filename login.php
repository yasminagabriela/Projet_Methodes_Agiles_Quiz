<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- <link rel="icon" type="image/png" href="images/background.png"> --> 
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="login-box">
                <h1>Bienvenue !</h1>
                <p>Sign in to continue to Quiz App</p>

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

</body>
</html>
