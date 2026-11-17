<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

if (($_SESSION['role'] ?? '') !== 'enseignant') {
    http_response_code(403);
    exit('Accès refusé');
}
$titreRole = 'Professeur';

$questionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// La question doit exister ET appartenir à un quiz de l'utilisateur connecté
$stmt = $pdo->prepare(
    'SELECT q.id, q.enonce, q.type, q.quiz_id, z.titre
     FROM questions q
     JOIN quiz z ON z.id = q.quiz_id
     WHERE q.id = ? AND z.user_id = ?'
);
$stmt->execute([$questionId, $_SESSION['user_id']]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    header('Location: index.php');
    exit;
}

// Numéro de la question dans son quiz (même ordre que quiz.php : ORDER BY id)
$stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE quiz_id = ? AND id <= ?');
$stmt->execute([$question['quiz_id'], $question['id']]);
$numero = (int) $stmt->fetchColumn();

// Les réponses de la question
$stmt = $pdo->prepare('SELECT texte, est_correcte FROM reponses WHERE question_id = ? ORDER BY id');
$stmt->execute([$questionId]);
$reponses = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $reponses[] = ['texte' => $r['texte'], 'est_correcte' => (bool) $r['est_correcte']];
}

// Données envoyées au JS pour remplir le formulaire
$donnees = [
    'id'       => (int) $question['id'],
    'quizId'   => (int) $question['quiz_id'],
    'enonce'   => $question['enonce'],
    'type'     => $question['type'],
    'reponses' => $reponses,
];
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
                <a href="quiz.php?id=<?= (int) $question['quiz_id'] ?>" class="return">‹ Retour au quiz</a>
            </div>

            <h1>Modifier la question <?= $numero ?></h1>

            <div class="quiz-badges">
                <span><?= htmlspecialchars($question['titre']) ?></span>
            </div>
        </section>

        <!-- Formulaire d'édition (mêmes classes et ids que le formulaire "Nouvelle question") -->
        <section class="quiz-questions">
            <div id="edit-question-panel" class="new-question-panel"
                data-question="<?= htmlspecialchars(json_encode($donnees, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                <h3>Modifier la question <?= $numero ?></h3>

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
                    <button type="button" id="new-save-question">Enregistrer la modification</button>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js?v=5"></script>
</body>

</html>