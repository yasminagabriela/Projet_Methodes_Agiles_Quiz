<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

if (($_SESSION['role'] ?? '') !== 'enseignant') {
    http_response_code(403);
    exit('Accès refusé');
}
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';

$quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$title = trim($_POST['title'] ?? '');
$description = '';
$questions = [];

// Enregistrer les paramètres du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $quizId > 0) {
    $newTitle = trim($_POST['title'] ?? '');
    $newDescr = trim($_POST['description'] ?? '');

    if ($newTitle !== '' && mb_strlen($newTitle) <= 100) {
        $stmt = $pdo->prepare('UPDATE quiz SET titre = ?, description = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$newTitle, $newDescr, $quizId, $_SESSION['user_id']]);

        header('Location: quiz.php?id=' . $quizId);
        exit;
    }
}
if ($quizId > 0) {
    // Le quiz doit exister ET appartenir à l'utilisateur connecté
    $stmt = $pdo->prepare('SELECT id, titre, description FROM quiz WHERE id = ? AND user_id = ?');
    $stmt->execute([$quizId, $_SESSION['user_id']]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        header('Location: index.php');
        exit;
    }

    $title = $quiz['titre'];
    $description = $quiz['description'] ?? '';

    // Les questions du quiz
    $stmt = $pdo->prepare('SELECT id, enonce, type FROM questions WHERE quiz_id = ? ORDER BY id');
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Les réponses de chaque question
    $stmtReponses = $pdo->prepare('SELECT texte, est_correcte FROM reponses WHERE question_id = ? ORDER BY id');
    foreach ($questions as &$q) {
        $stmtReponses->execute([$q['id']]);
        $q['reponses'] = $stmtReponses->fetchAll(PDO::FETCH_ASSOC);
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
                <span><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?></span>
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
                <input type="text" id="title" maxlength="100" name="title" value="<?= htmlspecialchars($title) ?>">

                <label for="description">Description (facultatif)</label>
                <textarea id="description" name="description"><?= htmlspecialchars($description) ?></textarea>

                <label>
                    <input type="checkbox" name="correction" checked>
                    Montrer la correction à l'étudiant après la soumission
                </label>

                <button type="submit">Enregistrer</button>
            </form>
        </details>

        <!-- Onglets -->
        <div class="quiz-tabs">
            <button type="button" class="active" id="tab-questions">Questions (<?= $nbQuestions ?>)</button>
            <?php if ($quizId > 0): ?>
                <button type="button" onclick="window.location.href='appercuQuiz.php?id=<?= $quizId ?>'">Aperçu</button>
            <?php endif; ?>
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

        <!-- Questions -->
        <section class="quiz-questions">

            <div class="questions-empty" <?= $nbQuestions > 0 ? 'hidden' : '' ?>>
                <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                <h3>Aucune question pour le moment</h3>
                <p>Ajoutez votre première question pour construire le quiz.</p>
            </div>

            <!-- Les questions ajoutées apparaissent ici -->
            <div id="questions-list">
                <?php foreach ($questions as $i => $q): ?>
                    <div class="question-block">
                        <div class="question-header">
                            <span class="q-numero"><?= $i + 1 ?></span>
                            <h4 class="q-enonce"><?= htmlspecialchars($q['enonce']) ?></h4>
                            <span class="q-type"><?= $q['type'] === 'choix_multiple' ? 'QCM' : 'Vrai/Faux' ?></span>
                        </div>

                        <div class="q-reponses">
                            <?php foreach ($q['reponses'] as $j => $r): ?>
                                <div class="reponse-row<?= $r['est_correcte'] ? ' correcte' : '' ?>">
                                    <span class="reponse-lettre"><?= chr(65 + $j) ?></span>
                                    <span><?= htmlspecialchars($r['texte']) ?></span>
                                    <?php if ($r['est_correcte']): ?>
                                        <span class="bonne-reponse">✓ Bonne réponse</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Interface de création d'une question -->
            <div id="new-question-panel" class="new-question-panel" hidden>
                <h3>Nouvelle question</h3>

                <div class="question-types">
                    <button type="button" class="selected" data-type="choix_multiple">
                        Choix multiples
                    </button>
                    <button type="button" data-type="vrai_faux">
                        Vrai ou faux
                    </button>
                </div>

                <label for="new-question-text">Énoncé</label>
                <textarea id="new-question-text" placeholder="Écrivez votre question ici"></textarea>

                <p id="new-question-instructions">Entrez 2 à 5 réponses possibles et choisissez la bonne.</p>

                <div id="new-question-answers"></div>

                <button type="button" id="new-add-answer">+ Ajouter une réponse</button>

                <div class="question-actions">
                    <button type="button" id="new-cancel-question">Annuler</button>
                    <button type="button" id="new-save-question">Ajouter la question</button>
                </div>
            </div>

            <!-- Modèle d'une question : utilisé par le JS -->
            <template id="question-template">
                <div class="question-block">

                    <div class="question-header">
                        <span class="q-numero"></span>
                        <h4 class="q-enonce"></h4>
                        <span class="q-type"></span>
                    </div>

                    <div class="q-reponses"></div>

                    <div class="question-actions">
                        <button type="button" class="q-modifier">Modifier</button>
                        <button type="button" class="q-supprimer">Supprimer</button>
                    </div>

                </div>
            </template>

            <?php if ($quizId === 0): ?>
                <button type="button" class="add-question">+ Ajouter une question</button>
                <button type="button" id="save-quiz">Enregistrer le quiz</button>
            <?php endif; ?>

        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js?v=3"></script>
</body>

</html>