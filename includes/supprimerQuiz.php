<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

// utilite pour répondre en JSON
function repondre(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// POST 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, ['erreur' => 'Méthode non autorisée']);
}

// connecté & enseignant
if (!is_logged_in()) {
    repondre(401, ['erreur' => 'Non connecté']);
}
if (($_SESSION['role'] ?? '') !== 'enseignant') {
    repondre(403, ['erreur' => 'Réservé aux enseignants']);
}

// Lire JSON : { "id": 5 }
$data = json_decode(file_get_contents('php://input'), true);
$quizId = is_array($data) ? (int) ($data['id'] ?? 0) : 0;
if ($quizId <= 0) {
    repondre(400, ['erreur' => 'Quiz invalide']);
}

try {
    // Quiz doit exister & appartenir à l'utilisateur connecté
    $stmt = $pdo->prepare('SELECT id FROM quiz WHERE id = ? AND user_id = ?');
    $stmt->execute([$quizId, $_SESSION['user_id']]);

    if (!$stmt->fetch()) {
        repondre(404, ['erreur' => 'Quiz introuvable']);
    }

    // Supprimer les réponses, puis les questions, puis le quiz (transaction)
    $pdo->beginTransaction();
    $pdo->prepare(
        'DELETE FROM reponses WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)'
    )->execute([$quizId]);
    $pdo->prepare('DELETE FROM questions WHERE quiz_id = ?')->execute([$quizId]);
    $pdo->prepare('DELETE FROM quiz WHERE id = ?')->execute([$quizId]);
    $pdo->commit();

    repondre(200, ['ok' => true]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    repondre(500, ['erreur' => 'Erreur lors de la suppression du quiz']);
}