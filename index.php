<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$estEnseignant = ($_SESSION['role'] ?? '') === 'enseignant';
$titreRole = $estEnseignant ? 'Professeur' : 'Étudiant(e)';

if ($estEnseignant) {
    $stmt = $pdo->prepare("SELECT id, titre, description FROM quiz WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->query("SELECT id, titre, description FROM quiz ORDER BY id DESC");
}
$quizList = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <div class="dashboard-user">
        <span class="current-user"><?= $titreRole; ?></span>
        <button type="button" class="deconnecter" onclick="window.location.href='logout.php'">Se déconnecter</button>
    </div>

    <main class="dashboard-content">

        <?php if ($estEnseignant): ?>

            <section class="dashboard-banner">
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Créez vos quiz, publiez-les et suivez les résultats de vos étudiants.</p>

                <form class="quiz-form" action="quiz.php" method="post">
                    <input type="text" name="title" placeholder="Titre du nouveau quiz" maxlength="100" required>
                    <button type="submit">Créer un quiz</button>
                </form>
            </section>

            <div class="stats">
                <div class="stat-card created">
                    <h2><?= count($quizList) ?></h2>
                    <p>Quiz créés</p>
                </div>
                <div class="stat-card published">
                    <h2>0</h2>
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
            <!-- Liste des quiz -->
            <?php if (empty($quizList)): ?>
                <p>Vous n'avez pas encore créé de quiz.</p>
            <?php else: ?>
                <div class="quiz-list">
                    <?php foreach ($quizList as $q): ?>
                        <a class="quiz-card" href="quiz.php?id=<?= (int) $q['id'] ?>">
                            <h3><?= htmlspecialchars($q['titre']) ?></h3>
                            <p><?= htmlspecialchars($q['description'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <section class="dashboard-banner">
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Choisissez un quiz pour commencer.</p>
            </section>

            <h2>Quiz disponibles</h2>
            <!-- Liste des quiz -->
            <?php if (empty($quizList)): ?>
                <p>Aucun quiz n'est disponible pour le moment.</p>
            <?php else: ?>
                <div class="quiz-list">
                    <?php foreach ($quizList as $q): ?>
                        <a class="quiz-card" href="passer_quiz.php?id=<?= (int) $q['id'] ?>">
                            <h3><?= htmlspecialchars($q['titre']) ?></h3>
                            <p><?= htmlspecialchars($q['description'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>

</html>