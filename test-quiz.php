<?php
require 'includes/db.php';

// Récupérer le quiz de test
$quiz = $pdo->query("SELECT * FROM quiz WHERE titre = 'Quiz test'")->fetch();

// Récupérer ses questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quiz['id']]);
$questions = $stmt->fetchAll();

// Préparer la requête des réponses (réutilisée pour chaque question)
$stmtRep = $pdo->prepare("SELECT * FROM reponses WHERE question_id = ? ORDER BY id");
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title><?= htmlspecialchars($quiz['titre']) ?></title></head>
<body>
    <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
    <p><?= htmlspecialchars($quiz['description']) ?></p>

    <form method="post" action="corriger.php">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

            <?php foreach ($questions as $i => $q): ?>
            <?php
            $stmtRep->execute([$q['id']]);
            $reponses = $stmtRep->fetchAll();

            // Combien de bonnes réponses ? Si plus d'une : cases à cocher, sinon boutons radio
            $nbCorrectes = count(array_filter($reponses, fn($r) => $r['est_correcte']));
            $typeInput = $nbCorrectes > 1 ? 'checkbox' : 'radio';
            ?>
            <fieldset>
                <legend><?= $i + 1 ?>. <?= htmlspecialchars($q['enonce']) ?></legend>

                <?php foreach ($reponses as $r): ?>
                    <label>
                        <input type="<?= $typeInput ?>"
                               name="reponses[<?= $q['id'] ?>][]"
                               value="<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['texte']) ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit">Soumettre</button>
    </form>
</body>
</html>