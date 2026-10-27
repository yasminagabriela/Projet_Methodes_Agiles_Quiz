<?php
require 'includes/db.php';

$quizId = (int) ($_POST['quiz_id'] ?? 0);
$choix = $_POST['reponses'] ?? [];   // [id_question => [id_reponse, ...]]

// Récupérer les questions du quiz
$stmt = $pdo->prepare("SELECT id, enonce FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

// Récupérer les bonnes réponses d'une question
$stmtBonnes = $pdo->prepare("SELECT id FROM reponses WHERE question_id = ? AND est_correcte = 1");

$score = 0;
foreach ($questions as $q) {
    $stmtBonnes->execute([$q['id']]);
    $bonnes = array_map('intval', $stmtBonnes->fetchAll(PDO::FETCH_COLUMN));
    $donnees = array_map('intval', $choix[$q['id']] ?? []);

    sort($bonnes);
    sort($donnees);

    // Tout ou rien : il faut cocher exactement les bonnes réponses
    if ($bonnes === $donnees) {
        $score++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Résultat</title>
</head>

<body>
    <h1>Résultat : <?= $score ?> / <?= count($questions) ?></h1>
    <a href="test_quiz.php">Recommencer</a>
</body>

</html>