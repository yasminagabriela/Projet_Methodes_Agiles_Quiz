<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Vérifier que des résultats existent
if (!isset($_SESSION['quiz_resultats'])) {
    header('Location: index.php');
    exit;
}

// Récupérer les résultats du quiz
$resultats = $_SESSION['quiz_resultats'];
unset($_SESSION['quiz_resultats']);

$quizTitre = $resultats['quizTitre'];
$score = $resultats['score'];
$total = $resultats['total'];
$pourcentage = $resultats['pourcentage'];
$correction = $resultats['correction'];

// Message selon le résultat
$message = $pourcentage >= 80
    ? "Excellent travail !"
    : ($pourcentage >= 50
        ? "Bien joué !"
        : "Continuez à vous entraîner !");

// Calcul du cercle de progression
$circonference = 2 * pi() * 54;
$progression = $circonference * $pourcentage / 100;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation du quiz - Qivvi</title>
    <link rel="stylesheet" href="css/styles.css?v=9">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="quiz-confirmation-page">
        <section class="quiz-result-banner">

            <div class="quiz-score-ring"
                role="img"
                aria-label="Score : <?= $score ?> sur <?= $total ?>">

                <svg viewBox="0 0 140 140" aria-hidden="true">
                    <circle
                        cx="70" cy="70" r="54"
                        fill="none"
                        stroke="currentColor"
                        stroke-opacity="0.18"
                        stroke-width="12" />

                    <circle
                        cx="70" cy="70" r="54"
                        fill="none"
                        stroke="var(--accent)"
                        stroke-width="12"
                        stroke-linecap="round"
                        stroke-dasharray="<?= $progression ?> <?= $circonference ?>"
                        transform="rotate(-90 70 70)" />
                </svg>
                <span class="quiz-score-number"><?= $score ?> / <?= $total ?></span>
            </div>
            <h1>Merci ! Vos réponses sont enregistrées</h1>

            <p>
                <?= htmlspecialchars($message) ?>
                <?= $pourcentage ?> % de bonnes réponses au quiz
                « <?= htmlspecialchars($quizTitre) ?> ».
            </p>
        </section>
        <section class="quiz-correction">
            <h2>Correction</h2>

            <?php foreach ($correction as $index => $question): ?>

                <?php $estCorrecte = $question['est_correcte']; ?>

                <article class="quiz-correction-card <?= $estCorrecte ? 'is-correct' : 'is-incorrect' ?>">
                    <div class="quiz-correction-header">
                        <span class="quiz-correction-number"><?= $index + 1 ?></span>
                        <h3><?= htmlspecialchars($question['question']) ?></h3>
                        <span class="quiz-verdict"><?= $estCorrecte ? 'Correct' : 'Incorrect' ?></span>
                    </div>
                    <div class="quiz-correction-choices">

                        <?php foreach ($question['reponses'] as $choixIndex => $reponse): ?>

                            <?php
                            $bonneReponse = in_array(
                                (int) $reponse['id'],
                                $question['correcte'],
                                true
                            );

                            $maReponse = in_array(
                                (int) $reponse['id'],
                                $question['choisie'],
                                true
                            );

                            $classe = $bonneReponse
                                ? 'correct-answer'
                                : ($maReponse ? 'wrong-answer' : '');
                            ?>

                            <div class="quiz-correction-choice <?= $classe ?>">
                                <span class="quiz-correction-letter"><?= chr(65 + $choixIndex) ?></span>
                                <span class="quiz-correction-text"><?= htmlspecialchars($reponse['texte']) ?></span>

                                <?php if ($bonneReponse): ?>
                                    <span class="quiz-answer-status">✓ <?= $maReponse ? 'Votre réponse' : 'Bonne réponse' ?></span>
                                <?php elseif ($maReponse): ?>
                                    <span class="quiz-answer-status">✕ Votre réponse</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

            <?php endforeach; ?>

        </section>

        <div class="quiz-confirmation-actions">
            <a href="index.php" class="quiz-back-button">Retour à l'accueil</a>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>

</html>