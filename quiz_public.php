<?php
// Afficher les erreurs pendant le développement
ini_set('display_errors', '1');
error_reporting(E_ALL);

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

// Récupérer le quiz publié
$stmt = $pdo->prepare("
    SELECT id, titre, description
    FROM quiz
    WHERE id = ? AND statut = 'publie'
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le quiz existe
if (!$quiz) {
    header('Location: index.php');
    exit;
}

// Le formulaire a été envoyé (après la confirmation) : retour à l'accueil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // header('Location: index.php?soumis=1');
    header('Location: quiz_confirmation.php');
    exit;
}

// Récupérer les questions
$stmt = $pdo->prepare("
    SELECT id, enonce, type
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id
");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les requêtes pour les réponses
$stmtReponses = $pdo->prepare("
    SELECT id, texte
    FROM reponses
    WHERE question_id = ?
    ORDER BY id
");

// Ajouter les réponses à chaque question
foreach ($questions as &$question) {
    $stmtReponses->execute([$question['id']]);
    $question['reponses'] = $stmtReponses->fetchAll(PDO::FETCH_ASSOC);

    $question['plusieurs'] = $question['type'] === 'choix_multiple';
}
unset($question);

$nbQuestions = count($questions);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?> - Qivvi</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>

<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="quiz-public-page">

        <!-- Bannière du quiz -->
        <section class="quiz-public-banner">
            <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
            <p><?= htmlspecialchars($quiz['description'] ?? '') ?></p>

            <div class="quiz-public-info">
                <span><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?></span>
                <!-- <span>1 seule tentative</span> -->
            </div>
        </section>

        <!-- Progression -->
        <?php if ($nbQuestions > 0): ?>
            <section class="quiz-progress">
                <div class="quiz-progress-header">
                    <h2>Votre progression</h2>
                    <span id="progress-text">0 / <?= $nbQuestions ?> réponses</span>
                </div>

                <div class="quiz-progress-bar">
                    <div id="progress-fill"></div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Questions -->
        <section class="quiz-public-content">
            <?php if ($nbQuestions === 0): ?>
                <p>Aucune question dans ce quiz pour le moment.</p>
            <?php else: ?>
                <form method="post" id="quiz-public-form">
                    <?php foreach ($questions as $index => $question): ?>
                        <fieldset class="quiz-question-card">
                            <legend class="quiz-sr-only">Question <?= $index + 1 ?></legend>

                            <div class="quiz-question-header">
                                <span class="quiz-question-number"><?= $index + 1 ?></span>
                                <h3><?= htmlspecialchars($question['enonce']) ?></h3>
                            </div>

                            <?php if ($question['plusieurs']): ?>
                                <p class="quiz-choice-hint">Plusieurs réponses possibles</p>
                            <?php endif; ?>

                            <div class="quiz-choices">
                                <?php foreach ($question['reponses'] as $choixIndex => $reponse): ?>
                                    <label class="quiz-choice">
                                        <input
                                            type="<?= $question['plusieurs'] ? 'checkbox' : 'radio' ?>"
                                            name="reponses[<?= (int) $question['id'] ?>][]"
                                            value="<?= (int) $reponse['id'] ?>">

                                        <span class="quiz-choice-letter"><?= chr(65 + $choixIndex) ?></span>
                                        <span class="quiz-choice-text"><?= htmlspecialchars($reponse['texte']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>

                    <p id="quiz-erreur" class="quiz-error" hidden>
                        Veuillez répondre à toutes les questions avant de soumettre le quiz.
                    </p>
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

            // Barre de progression : se met à jour à chaque réponse cochée ou décochée
            formulaireQuiz.addEventListener('change', function() {
                const cartes = formulaireQuiz.querySelectorAll('.quiz-question-card');
                let repondues = 0;

                cartes.forEach(function(carte) {
                    if (carte.querySelector('input:checked')) {
                        repondues++;
                    }
                });

                document.getElementById('progress-text').textContent =
                    repondues + ' / ' + cartes.length + ' réponses';

                document.getElementById('progress-fill').style.width =
                    (repondues / cartes.length * 100) + '%';
            });

            formulaireQuiz.addEventListener('submit', function(e) {

                // On bloque l'envoi pour d'abord demander la confirmation
                e.preventDefault();

                // Compter les questions qui ont au moins une réponse cochée
                const cartes = formulaireQuiz.querySelectorAll('.quiz-question-card');
                let repondues = 0;

                // Vérifier chaque question
                cartes.forEach(function(carte) {
                    if (carte.querySelector('input:checked')) {
                        repondues++;
                    }
                });

                const total = cartes.length;
                const erreur = document.getElementById('quiz-erreur');

                // Vérifier si toutes les questions ont une réponse
                if (repondues < total) {
                    erreur.hidden = false;
                    return;
                }

                // Toutes les quetions sont répondues
                erreur.hidden = true;

                let message = 'Vous avez répondu à ' + repondues + ' question' +
                    (repondues > 1 ? 's' : '') + ' sur ' + total + '.';

                message += ' Une fois envoyées, vos réponses ne pourront plus être modifiées.';

                document.getElementById('soumettre-message').textContent = message;

                dialogueSoumettre.returnValue = '';
                dialogueSoumettre.showModal();
            });

            // Fermeture de la boîte : on envoie seulement si l'étudiant a cliqué « Soumettre »
            dialogueSoumettre.addEventListener('close', function() {
                if (dialogueSoumettre.returnValue === 'envoyer') {
                    formulaireQuiz.submit(); // submit() ne redéclenche pas l'événement 'submit'
                }
            });

            // Cliquer en dehors de la boîte = annuler
            dialogueSoumettre.addEventListener('click', function(e) {
                if (e.target === dialogueSoumettre) {
                    dialogueSoumettre.close();
                }
            });
        }
    </script>

</body>

</html>