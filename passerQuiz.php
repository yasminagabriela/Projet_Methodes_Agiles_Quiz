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

// Récupérer le quiz : seulement s'il est publié
$stmt = $pdo->prepare("
    SELECT id, titre, description
    FROM quiz
    WHERE id = ? AND statut = 'publie'
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

// Quiz inexistant ou non publié : retour à l'accueil
if (!$quiz) {
    header('Location: index.php');
    exit;
}

// Le formulaire a été envoyé (après la confirmation) : retour à l'accueil
// (l'enregistrement des réponses et le calcul du score restent à faire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: index.php?soumis=1');
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

// Récupérer les réponses de chaque question.
// On ne lit jamais est_correcte dans la page : seulement le nombre de bonnes
// réponses, pour savoir si l'étudiant peut en cocher une ou plusieurs.
$stmtReponses = $pdo->prepare("
    SELECT id, texte
    FROM reponses
    WHERE question_id = ?
    ORDER BY id
");
$stmtNbCorrectes = $pdo->prepare("
    SELECT COUNT(*)
    FROM reponses
    WHERE question_id = ? AND est_correcte = 1
");

foreach ($questions as &$q) {
    $stmtReponses->execute([$q['id']]);
    $q['reponses'] = $stmtReponses->fetchAll(PDO::FETCH_ASSOC);

    $stmtNbCorrectes->execute([$q['id']]);
    $q['plusieurs'] = (int) $stmtNbCorrectes->fetchColumn() > 1;
}
unset($q);

$nbQuestions = count($questions);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?> | Qivvi</title>
    <link rel="stylesheet" href="css/styles.css?v=11">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="quiz-public-page">
        <!-- Bannière du quiz -->
        <section class="quiz-public-banner">
            <a href="index.php" class="return">‹ Retour aux quiz</a>
            <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
            <p><?= htmlspecialchars($quiz['description'] ?? '') ?></p>

            <div class="quiz-public-info">
                <span><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?></span>
            </div>
        </section>

        <!-- Questions -->
        <section class="quiz-public-content">
            <?php if ($nbQuestions === 0): ?>
                <p>Aucune question dans ce quiz pour le moment.</p>
            <?php else: ?>
                <form method="post" id="quiz-public-form">
                    <?php foreach ($questions as $i => $q): ?>
                        <fieldset class="quiz-question-card">
                            <legend class="quiz-sr-only">Question <?= $i + 1 ?></legend>

                            <div class="quiz-question-header">
                                <span class="quiz-question-number"><?= $i + 1 ?></span>
                                <h3><?= htmlspecialchars($q['enonce']) ?></h3>
                            </div>

                            <?php if ($q['plusieurs']): ?>
                                <p class="quiz-choice-hint">Plusieurs réponses possibles</p>
                            <?php endif; ?>

                            <div class="quiz-choices">
                                <?php foreach ($q['reponses'] as $j => $r): ?>
                                    <label class="quiz-choice">
                                        <input
                                            type="<?= $q['plusieurs'] ? 'checkbox' : 'radio' ?>"
                                            name="reponses[<?= (int) $q['id'] ?>][]"
                                            value="<?= (int) $r['id'] ?>">

                                        <span class="quiz-choice-letter"><?= chr(65 + $j) ?></span>
                                        <span class="quiz-choice-text"><?= htmlspecialchars($r['texte']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>

                    <button type="submit" class="quiz-submit-btn">Soumettre mes réponses</button>
                </form>
            <?php endif; ?>
        </section>

        <!-- Boîte de confirmation avant l'envoi des réponses -->
        <dialog id="soumettre-dialog" class="confirm-dialog" aria-labelledby="soumettre-titre">
            <form method="dialog">
                <h3 id="soumettre-titre">Soumettre vos réponses ?</h3>
                <p id="soumettre-message"></p>

                <div class="confirm-actions">
                    <button type="submit" value="annuler" class="confirm-cancel">Annuler</button>
                    <button type="submit" value="envoyer" class="confirm-envoyer">Soumettre</button>
                </div>
            </form>
        </dialog>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>

    <script>
        const formulaireQuiz = document.getElementById('quiz-public-form');
        const dialogueSoumettre = document.getElementById('soumettre-dialog');

        if (formulaireQuiz) {
            formulaireQuiz.addEventListener('submit', function (e) {
                // On bloque l'envoi pour d'abord demander la confirmation
                e.preventDefault();

                // Compter les questions qui ont au moins une réponse cochée
                const total = formulaireQuiz.querySelectorAll('.quiz-question-card').length;
                let repondues = 0;
                formulaireQuiz.querySelectorAll('.quiz-question-card').forEach(function (carte) {
                    if (carte.querySelector('input:checked')) {
                        repondues++;
                    }
                });

                let message = 'Vous avez répondu à ' + repondues + ' question'
                    + (repondues > 1 ? 's' : '') + ' sur ' + total + '.';
                if (repondues < total) {
                    message += ' Certaines questions sont sans réponse.';
                }
                message += ' Une fois envoyées, vos réponses ne pourront plus être modifiées.';

                document.getElementById('soumettre-message').textContent = message;
                dialogueSoumettre.returnValue = '';   // sinon Échap garderait la valeur du dernier clic
                dialogueSoumettre.showModal();
            });

            // Fermeture de la boîte : on envoie seulement si l'étudiant a cliqué « Soumettre »
            dialogueSoumettre.addEventListener('close', function () {
                if (dialogueSoumettre.returnValue === 'envoyer') {
                    formulaireQuiz.submit();   // submit() ne redéclenche pas l'événement 'submit'
                }
            });

            // Cliquer en dehors de la boîte = annuler
            dialogueSoumettre.addEventListener('click', function (e) {
                if (e.target === dialogueSoumettre) {
                    dialogueSoumettre.close();
                }
            });
        }
    </script>
</body>

</html>
