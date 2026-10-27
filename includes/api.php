<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Small helper, like res.status(code).json(data)
function repondre(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}



// 1. Accept POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, ['erreur' => 'Méthode non autorisée']);
}




// 2. Must be logged in AND a teacher
if (!is_logged_in()) {
    repondre(401, ['erreur' => 'Non connecté']);
}
if (($_SESSION['role'] ?? '') !== 'enseignant') {
    repondre(403, ['erreur' => 'Réservé aux enseignants']);
}




// 3. Read the JSON body
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    repondre(400, ['erreur' => 'JSON invalide']);
}




// -------------- 4. Validate -------------------

// titre
$titre = trim($data['titre'] ?? '');
if ($titre === '' || mb_strlen($titre) > 100) {
    repondre(400, ['erreur' => 'Le titre est obligatoire (100 caractères max)']);
}


// description
$description = trim($data['description'] ?? '');
$description = $description === '' ? null : $description;




// questions 
$questions = $data['questions'] ?? null;
if (!is_array($questions) || count($questions) === 0) {
    repondre(400, ['erreur' => 'Le quiz doit contenir au moins une question']);
}

$typesValides = ['choix_multiple', 'vrai_faux'];

foreach ($questions as $i => $q) {
    $num = $i + 1;
    if (!is_array($q) || trim($q['enonce'] ?? '') === '') {
        repondre(400, ['erreur' => "Question $num : l'énoncé est obligatoire"]);
    }
    if (!in_array($q['type'] ?? '', $typesValides, true)) {
        repondre(400, ['erreur' => "Question $num : type invalide"]);
    }


    // entre 2 et 5 réponses
    $reponses = $q['reponses'] ?? null;
    if (!is_array($reponses) || count($reponses) < 2 || count($reponses) > 5) {
        repondre(400, ['erreur' => "Question $num : il faut entre 2 et 5 réponses"]);
    }

    // une reponse doit estcorrect
    $nbCorrectes = 0;
    foreach ($reponses as $r) {
        $texte = trim($r['texte'] ?? '');
        if ($texte === '' || mb_strlen($texte) > 255) {
            repondre(400, ['erreur' => "Question $num : chaque réponse doit avoir un texte (255 caractères max)"]);
        }
        if (($r['est_correcte'] ?? false) === true) {
            $nbCorrectes++;
        }
    }
    if ($nbCorrectes === 0) {
        repondre(400, ['erreur' => "Question $num : au moins une réponse doit être correcte"]);
    }
}



// 5. Insert everything in a transaction
try {
    $pdo->beginTransaction();

    // prepare est utile avec ? pour prévenir des injections sql
    $stmt = $pdo->prepare('INSERT INTO quiz (titre, description, user_id) VALUES (?, ?, ?)');
    $stmt->execute([$titre, $description, $_SESSION['user_id']]);
    $quizId = $pdo->lastInsertId();

    // Prepared once, executed in the loops
    $stmtQuestion = $pdo->prepare('INSERT INTO questions (enonce, type, quiz_id) VALUES (?, ?, ?)');
    $stmtReponse  = $pdo->prepare('INSERT INTO reponses (texte, est_correcte, question_id) VALUES (?, ?, ?)');

    foreach ($questions as $q) {
        $stmtQuestion->execute([trim($q['enonce']), $q['type'], $quizId]);
        $questionId = $pdo->lastInsertId();

        foreach ($q['reponses'] as $r) {
            $estCorrect = ($r['est_correcte'] ?? false) === true ? 1 : 0;
            $stmtReponse->execute([trim($r['texte']), $estCorrect, $questionId]);
        }
    }

    $pdo->commit();
    repondre(201, ['id' => (int)$quizId]);
} catch (PDOException $e) {
    $pdo->rollBack();
    repondre(500, ['erreur' => 'Erreur lors de la création du quiz']);
}
