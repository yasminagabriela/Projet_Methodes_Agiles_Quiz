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

        <div class="dashboard-user">
            <span class="current-user">Professeur</span>
            <button type="button" class="deconnecter" onclick="window.location.href='login.php'">Se déconnecter</button>
        </div>

        <main class="dashboard-content">
            <section class="dashboard-banner">
                <h1>Bonjour, Professeur</h1>
                <p>Créez vos quiz, publiez-les et suivez les résultats de vos étudiants.</p>

                <form class="quiz-form">
                    <input type="text" placeholder="Titre du nouveau quiz">
                    <button type="submit">Créer un quiz</button>
                </form>
            </section>
        </main>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
    </body>
</html>