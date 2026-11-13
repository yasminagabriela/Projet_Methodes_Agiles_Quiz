
<?php
// Afficher les erreurs pendant le développement
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Données temporaires pour tester l'interface
$quiz = [
    'id' => 1,
    'titre' => 'Quiz Scrum',
    'description' => 'Les bases de la méthode Scrum'
];
$questions = [
    [
        'id' => 1,
        'texte' => 'Qui facilite le travail de l’équipe Scrum ?',
        'reponses' => [
            'Le Scrum Master',
            'Le client',
            'Le développeur principal',
            'Le directeur'
        ]
    ],
    [
        'id' => 2,
        'texte' => 'Quel événement permet de planifier le travail du sprint ?',
        'reponses' => [
            'La rétrospective',
            'Le Sprint Planning',
            'Le Daily Scrum',
            'La Sprint Review'
        ]
    ]
];

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
            <p><?= htmlspecialchars($quiz['description']) ?></p>

            <div class="quiz-public-info">
                <span><?= $nbQuestions ?> questions</span>
                <span>1 seule tentative</span>
            </div>
        </section>

        <!-- Progression -->
        <section class="quiz-progress">
            <div class="quiz-progress-header">
                <h2>Votre progression</h2>
                <span id="progress-text">
                    0 / <?= $nbQuestions ?> réponses
                </span>
            </div>

            <div class="quiz-progress-bar">
                <div id="progress-fill"></div>
            </div>
        </section>

        <!-- Questions -->
        <section class="quiz-public-content">
            <form id="quiz-public-form">

                <?php foreach ($questions as $index => $question): ?>
                    <fieldset class="quiz-question-card">

                        <legend class="quiz-sr-only">
                            Question <?= $index + 1 ?>
                        </legend>

                        <div class="quiz-question-header">
                            <span class="quiz-question-number">
                                <?= $index + 1 ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($question['texte']) ?>
                            </h3>
                        </div>

                        <div class="quiz-choices">
                            <?php foreach ($question['reponses'] as $choixIndex => $reponse): ?>
                                <label class="quiz-choice">
                                    <input
                                        type="radio"
                                        name="question_<?= (int) $question['id'] ?>"
                                        value="<?= $choixIndex ?>">

                                    <span class="quiz-choice-letter">
                                        <?= chr(65 + $choixIndex) ?>
                                    </span>

                                    <span class="quiz-choice-text">
                                        <?= htmlspecialchars($reponse) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    </fieldset>
                <?php endforeach; ?>

                <button type="button" class="quiz-submit-btn" disabled>
                    Soumettre mes réponses
                </button>
            </form>
        </section>

    </main>


    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>

    <script>
        const choix = document.querySelectorAll(
            '#quiz-public-form input[type="radio"]'
        );

        const total = <?= $nbQuestions ?>;

        choix.forEach(input => {
            input.addEventListener('change', () => {

                const questionsRepondues = new Set();

                document.querySelectorAll(
                    '#quiz-public-form input[type="radio"]:checked'
                ).forEach(reponse => {
                    questionsRepondues.add(reponse.name);
                });

                const nombre = questionsRepondues.size;

                document.getElementById('progress-text').textContent =
                    nombre + ' / ' + total + ' réponses';

                document.getElementById('progress-fill').style.width =
                    (nombre / total * 100) + '%';
            });
        });
    </script>

</body>

</html>
