<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant';
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
    <?php include __DIR__ . '/includes/header.php'; 
    ?>

        <div class="dashboard-user">
            <span class="current-user"><?= $titreRole; ?></span>
            <button type="button" class="deconnecter" onclick="window.location.href='logout.php'">Se déconnecter</button>
        </div>

        <main class="dashboard-content">
            <section class="dashboard-banner">
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Créez vos quiz, publiez-les et suivez les résultats de vos étudiants.</p>

                <form class="quiz-form">
                    <input type="text" placeholder="Titre du nouveau quiz">
                    <button type="submit">Créer un quiz</button>
                </form>
            </section>

            <div class="stats">
                <div class="stat-card created">
                    <h2>1</h2>
                    <p>Quiz créés</p>
                </div>
                <div class="stat-card published">
                    <h2>1</h2>
                    <p>Quiz publiés</p>
                </div>
                <div class="stat-card drafts">
                    <h2>0</h2>
                    <p>Brouillons</p>
                </div>
                <div class="stat-card submitted">
                    <h2>0</h2>
                    <p>Quiz remis</p>
                </div>
            </div>
            <h2>Mes quiz</h2>
        </main>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
    </body>
</html>