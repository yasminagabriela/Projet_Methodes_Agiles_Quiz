<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Petit helper pour répondre en JSON
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

// Lire le JSON : { "id": 12 }
$data = json_decode(file_get_contents('php://input'), true);
$questionId = is_array($data) ? (int) ($data['id'] ?? 0) : 0;
if ($questionId <= 0) {
    repondre(400, ['erreur' => 'Question invalide']);
}

try {
    // La question doit exister ET appartenir à un quiz de l'utilisateur connecté
    $stmt = $pdo->prepare(
        'SELECT q.id
         FROM questions q
         JOIN quiz z ON z.id = q.quiz_id
         WHERE q.id = ? AND z.user_id = ?'
    );
    $stmt->execute([$questionId, $_SESSION['user_id']]);

    if (!$stmt->fetch()) {
        repondre(404, ['erreur' => 'Question introuvable']);
    }

    // Supprimer les réponses puis la question 
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM reponses WHERE question_id = ?')->execute([$questionId]);
    $pdo->prepare('DELETE FROM questions WHERE id = ?')->execute([$questionId]);
    $pdo->commit();

    repondre(200, ['ok' => true]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    repondre(500, ['erreur' => 'Erreur lors de la suppression de la question']);
}