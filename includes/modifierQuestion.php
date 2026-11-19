<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

function repondre(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// POST seulement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, ['erreur' => 'Méthode non autorisée']);
}

// Il faut être connecté & enseignant
if (!is_logged_in()) {
    repondre(401, ['erreur' => 'Non connecté']);
}
if (($_SESSION['role'] ?? '') !== 'enseignant') {
    repondre(403, ['erreur' => 'Réservé aux enseignants']);
}

// Lire le JSON : { id, enonce, type, reponses: [{ texte, est_correcte }] }
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    repondre(400, ['erreur' => 'JSON invalide']);
}

$questionId = (int) ($data['id'] ?? 0);
if ($questionId <= 0) {
    repondre(400, ['erreur' => 'Question invalide']);
}

// Validation (mêmes règles que api.php) 

$enonce = trim($data['enonce'] ?? '');
if ($enonce === '') {
    repondre(400, ['erreur' => "L'énoncé est obligatoire"]);
}

$type = $data['type'] ?? '';
if (!in_array($type, ['choix_multiple', 'vrai_faux'], true)) {
    repondre(400, ['erreur' => 'Type de question invalide']);
}

// entre 2 et 5 réponses
$reponses = $data['reponses'] ?? null;
if (!is_array($reponses) || count($reponses) < 2 || count($reponses) > 5) {
    repondre(400, ['erreur' => 'Il faut entre 2 et 5 réponses']);
}

$nbCorrectes = 0;
foreach ($reponses as $r) {
    $texte = is_array($r) ? trim($r['texte'] ?? '') : '';
    if ($texte === '' || mb_strlen($texte) > 255) {
        repondre(400, ['erreur' => 'Chaque réponse doit avoir un texte (255 caractères max)']);
    }
    if (($r['est_correcte'] ?? false) === true) {
        $nbCorrectes++;
    }
}
if ($nbCorrectes === 0) {
    repondre(400, ['erreur' => 'Au moins une réponse doit être correcte']);
}
if ($type === 'vrai_faux' && (count($reponses) !== 2 || $nbCorrectes !== 1)) {
    repondre(400, ['erreur' => 'Un vrai/faux a 2 réponses avec seulement une correcte']);
}

try {
    // La question doit exister ET appartenir à un quiz de l'utilisateur connecté
    $stmt = $pdo->prepare(
        'SELECT q.id, q.quiz_id
         FROM questions q
         JOIN quiz z ON z.id = q.quiz_id
         WHERE q.id = ? AND z.user_id = ?'
    );
    $stmt->execute([$questionId, $_SESSION['user_id']]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        repondre(404, ['erreur' => 'Question introuvable']);
    }

    // Mettre à jour la question, puis remplacer ses réponses (transaction)
    $pdo->beginTransaction();

    $pdo->prepare('UPDATE questions SET enonce = ?, type = ? WHERE id = ?')
        ->execute([$enonce, $type, $questionId]);

    $pdo->prepare('DELETE FROM reponses WHERE question_id = ?')->execute([$questionId]);

    $stmtReponse = $pdo->prepare('INSERT INTO reponses (texte, est_correcte, question_id) VALUES (?, ?, ?)');
    foreach ($reponses as $r) {
        $estCorrect = ($r['est_correcte'] ?? false) === true ? 1 : 0;
        $stmtReponse->execute([trim($r['texte']), $estCorrect, $questionId]);
    }

    $pdo->commit();

    repondre(200, ['ok' => true, 'quiz_id' => (int) $question['quiz_id']]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    repondre(500, ['erreur' => 'Erreur lors de la modification de la question']);
}