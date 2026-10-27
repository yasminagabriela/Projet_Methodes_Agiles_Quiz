<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';
$title = trim($_POST['title'] ?? '');
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

    <!-- Les details du quiz -->
    <main class="quiz-details">
        <!-- Banniere du quiz -->
        <section class="quiz-banner">
            <div class="quiz-banner-top">
                <a href="index.php" class="return">‹ Mes quiz</a>
                <button type="button" class="delete">Supprimer le quiz</button>
            </div>

            <h1><?= htmlspecialchars($title) ?></h1>

            <div class="quiz-badges">
                <span>Brouillon</span>
                <span>0 question</span>
                <span>0 remis</span>
            </div>
        </section>

        <!-- Publication -->
        <section class="quiz-publication">
            <div>
                <h3>Ce quiz est en brouillon</h3>
                <p>Ajoutez vos questions, puis publiez-le pour obtenir un code à donner à vos étudiants.</p>
            </div>

            <button type="button">Publier le quiz</button>
        </section>

        <!-- Paramètres -->
        <details class="quiz-options">
            <summary>Paramètres du quiz</summary>

            <form method="post">
                <label for="title">Titre</label>
                <input type="text" id="title" name="title"
                    value="<?= htmlspecialchars($title ?? '') ?>">

                <label for="description">Description (facultatif)</label>
                <textarea id="description" name="description"></textarea>

                <label>
                    <input type="checkbox" name="correction" checked>
                    Montrer la correction à l'étudiant après la soumission
                </label>

                <button type="submit">Enregistrer</button>
            </form>
        </details>

        <!-- Onglets -->
        <div class="quiz-tabs">
            <button type="button" class="active">Questions (0)</button>
            <button type="button" onclick="window.location.href='appercuQuiz.php<?= isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '' ?>'">Résultats</button>
        </div>

        <!-- Questions -->
        <section class="quiz-questions">
            <div class="questions-empty">
                <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                <h3>Aucune question pour le moment</h3>
                <p>Ajoutez votre première question pour construire le quiz.</p>
            </div>

            <button type="button" class="add-question">
                + Ajouter une question
            </button>
        </section>
    </main>
            <!-- Questions -->
            <section class="quiz-questions" id="questions-empty">
                <div class="questions-empty">
                    <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                    <h3>Aucune question pour le moment</h3>
                    <p>Ajoutez votre première question pour construire le quiz.</p>
                </div>

                <button type="button" class="add-question" id="btn-add-question">
                    + Ajouter une question
                </button>
            </section>


            <!-- Formulaire de nouvelle question -->
            <div id="question-form" hidden>
            <h3>Nouvelle question</h3>
            <form>
                <label for="enonce">Énoncé</label>
                <textarea id="enonce" placeholder="Écrivez votre question ici"></textarea>

                <input type="text" placeholder="Réponse 1">
                <input type="text" placeholder="Réponse 2">

                <button type="button" id="btn-cancel">Annuler</button>
                <button type="submit">Ajouter la question</button>
            </form>
        </div>
        
        </main>

    <script src="js/script.js"></script>
</body>

</html>