<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';

// false = questions seulement ; true = montrer aussi les choix (sans la bonne réponse)
$afficherReponses = false;

$quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Le quiz doit exister ET appartenir à l'utilisateur connecté
$stmt = $pdo->prepare('SELECT id, titre FROM quiz WHERE id = ? AND user_id = ?');
$stmt->execute([$quizId, $_SESSION['user_id']]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, enonce, type FROM questions WHERE quiz_id = ? ORDER BY id');
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($afficherReponses) {
    // On ne lit que le texte : la bonne réponse n'arrive jamais dans la page
    $stmtReponses = $pdo->prepare('SELECT texte FROM reponses WHERE question_id = ? ORDER BY id');
    foreach ($questions as &$q) {
        $stmtReponses->execute([$q['id']]);
        $q['reponses'] = $stmtReponses->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($q);
}

$nbQuestions = count($questions);
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
        <!-- Bannière -->
        <section class="quiz-banner">
            <div class="quiz-banner-top">
                <a href="index.php" class="return">‹ Mes quiz</a>
            </div>

            <h1>Aperçu : <?= htmlspecialchars($quiz['titre']) ?></h1>

            <div class="quiz-badges">
                <span><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?></span>
            </div>
        </section>

        <!-- Onglets -->
        <div class="quiz-tabs">
            <button type="button" onclick="window.location.href='quiz.php?id=<?= $quizId ?>'">Questions</button>
            <button type="button" class="active" id="tab-questions">Aperçu</button>
            <button type="button" id="tab-results">Résultats</button>
        </div>
        
        <!-- Section Résultats -->
        <section class="quiz-results" hidden>
            <div class="results-stats">
                <div class="results-stat created">
                    <h2>0</h2>
                    <p>Quiz remis</p>
                </div>

                <div class="results-stat published">
                    <h2>–</h2>
                    <p>Moyenne</p>
                </div>

                <div class="results-stat drafts">
                    <h2>0</h2>
                    <p>En cours</p>
                </div>
            </div>

            <div class="results-empty">
                <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                <h3>Aucun résultat pour le moment</h3>
                <p>Les scores apparaîtront ici dès qu'un étudiant remettra le quiz.</p>
            </div>
        </section>

        <!-- Aperçu : ce que verra l'étudiant -->
        <section class="quiz-questions">
            <?php if ($nbQuestions === 0): ?>
                <div class="questions-empty">
                    <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                    <h3>Ce quiz ne contient pas encore de questions</h3>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $i => $q): ?>
                    <div class="question-block">
                        <div class="question-header">
                            <span class="q-numero"><?= $i + 1 ?></span>
                            <h4 class="q-enonce"><?= htmlspecialchars($q['enonce']) ?></h4>
                            <span class="q-type"><?= $q['type'] === 'choix_multiple' ? 'QCM' : 'Vrai/Faux' ?></span>
                        </div>

                        <?php if ($afficherReponses): ?>
                            <div class="q-reponses">
                                <?php foreach ($q['reponses'] as $j => $texte): ?>
                                    <div class="reponse-row">
                                        <span class="reponse-lettre"><?= chr(65 + $j) ?></span>
                                        <span><?= htmlspecialchars($texte) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="js/script.js"></script>
</body>

</html>