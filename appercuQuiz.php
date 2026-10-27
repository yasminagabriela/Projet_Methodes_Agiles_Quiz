<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';


require_login();
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';
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
    <!-- HEADER -->
    <?php include __DIR__ . '/includes/header.php'; ?>


    <!-- Nom de l'utilisateur + bouton de deconnexion -->
    <div class="dashboard-user">
        <span class="current-user"><?= $titreRole; ?></span>
        <button type="button" class="deconnecter" onclick="window.location.href='logout.php'">Se déconnecter</button>
    </div>


    <main class="quiz-details">
        <?php $idParam = isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''; ?>

        <!-- Banniere du quiz -->
        <section class="quiz-banner">
            <div class="quiz-banner-top">
                <a href="index.php" class="return">‹ Mes quiz</a>
            </div>

            <h2 class="quiz-title">Aperçu des quiz</h2>
        </section>

        <!-- Onglets -->
        <div class="quiz-tabs">
            <button type="button" onclick="window.location.href='quiz.php<?= $idParam ?>'">Questions</button>
            <button type="button" class="active">Résultats</button>
        </div>

        <!-- Questions -->
        <section class="quiz-questions">
            <?php if (empty($quizs)): ?>
                <div class="questions-empty">
                    <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                    <h3>Aucun quiz ou de questions ajoutées</h3>
                </div>
            <?php else: ?>
                <form method="post">
                    <!-- TODO: afficher les questions et réponses de $quizs -->
                    <button type="submit">Remettre</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script src="js/script.js"></script>

</body>

</html>