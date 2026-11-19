<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

require_login();

// Vérifier que l'utilisateur est un étudiant
if (($_SESSION['role'] ?? '') !== 'etudiant') {
    header('Location: index.php');
    exit;
}

// Récupérer l'identifiant du quiz
$quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Récupérer le quiz
$stmt = $pdo->prepare("
    SELECT id, titre, description
    FROM quiz
    WHERE id = ?
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier que le quiz existe
if (!$quiz) {
    header('Location: index.php');
    exit;
}

// Récupérer les questions du quiz
$stmt = $pdo->prepare("
    SELECT id, enonce, type
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id
");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?> | Qivvi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="quiz-details">
        <section class="quiz-banner">
            <div class="quiz-banner-top">
                <a href="index.php" class="return">Retour aux quiz</a>
            </div>
            <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
            <p><?= htmlspecialchars($quiz['description'] ?? '') ?></p>
        </section>

        <h2>Questions du quiz</h2>
        <?php if (empty($questions)): ?>
            <p>Aucune question dans ce quiz pour le moment.</p>
        <?php else: ?>
            <?php foreach ($questions as $i => $q): ?>
                <div class="question-block">
                    <div class="question-header">
                        <span class="q-numero"><?= $i + 1 ?></span>
                        <h3 class="q-enonce">
                            <?= htmlspecialchars($q['enonce']) ?>
                        </h3>
                        <span class="q-type">
                            <?= $q['type'] === 'choix_multiple' ? 'QCM' : 'Vrai/Faux' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>

</html>