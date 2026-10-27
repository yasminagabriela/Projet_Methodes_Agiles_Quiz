<?php
// Initialisation de la base de données
require 'includes/db.php';


// Créer la table 'users'
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'enseignant') NOT NULL DEFAULT 'etudiant'
)");

// Ajouter la colonne "role" à la table 'users' (À SUPPRIMER APRÈS QUE TOUT LE MONDE L'AIT FAIT)
$colonne = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
if (!$colonne) {
    $pdo->exec("ALTER TABLE users ADD role ENUM('etudiant', 'enseignant') NOT NULL DEFAULT 'etudiant'");
    $pdo->exec("UPDATE users SET role = 'enseignant' WHERE username = 'Farid'");
} elseif (stripos($colonne['Type'], 'enum') === false) {
    // La colonne existe déjà en VARCHAR : la convertir en ENUM (À SUPPRIMER APRÈS QUE TOUT LE MONDE L'AIT FAIT)
    $pdo->exec("ALTER TABLE users MODIFY role ENUM('etudiant', 'enseignant') NOT NULL DEFAULT 'etudiant'");
}

// Sécuriser les mots de passes
$hash = password_hash('Farid', PASSWORD_DEFAULT);
$pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES ('Farid', ?, 'enseignant')")->execute([$hash]);


// Créer la table 'quiz'
$pdo->exec("CREATE TABLE IF NOT EXISTS quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Créer la table 'questions'
$pdo->exec("CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enonce TEXT NOT NULL,
    type ENUM('choix_multiple', 'vrai_faux') NOT NULL,
    quiz_id INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
    ON DELETE CASCADE
)");

// Retirer la colonne "ordre" de la table 'questions' (À SUPPRIMER APRÈS QUE TOUT LE MONDE L'AIT FAIT)
$colonneOrdre = $pdo->query("SHOW COLUMNS FROM questions LIKE 'ordre'")->fetch();
if ($colonneOrdre) {
    $pdo->exec("ALTER TABLE questions DROP COLUMN ordre");
}

// Créer la table 'reponses'
$pdo->exec("CREATE TABLE IF NOT EXISTS reponses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texte VARCHAR(255) NOT NULL,
    est_correcte BOOL NOT NULL DEFAULT FALSE,
    question_id INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id)
    ON DELETE CASCADE
)");

// Créer un faux quiz pour tester (À SUPPRIMER APRÈS LES TESTS)

// 1. Récupérer l'id du compte enseignant, proprio du quiz
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'Farid'");
$stmt->execute();
$farid = $stmt->fetch();

// 2. Vérifier si le faux quiz existe déjà
$titreTest = 'Quiz test';
$stmt = $pdo->prepare("SELECT id FROM quiz WHERE titre = ? AND user_id = ?");
$stmt->execute([$titreTest, $farid['id']]);

// On créé le quiz seulement s'il n'y a aucun résultat
if (!$stmt->fetch()) {

    // 3. Les données du quiz : un tableau de questions, chacune avec son tableau de réponses
    //    Chaque réponse est de la forme [texte, est_correcte]
    $quizData = [
        [
            'enonce' => 'Combien de personnes y a-t-il dans notre équipe ?',
            'type' => 'choix_multiple',
            'reponses' => [
                ['5', true],
                ['2', false],
                ['9', false],
                ['4', false]
            ],
        ],
        [
            'enonce' => 'Le(s) Scrum Master(s) du SPRINT 01 et du SPRINT 02 ?',
            'type' => 'choix_multiple',
            'reponses' => [
                ['Talyssa', false],
                ['Yasmina', true],
                ['Sara', false],
                ['Maliha', true],
                ['Jaynelle', false]
            ],
        ],
        [
            // Les vrai/faux sont stockés comme deux réponses
            'enonce' => 'Notre site web Qivvi est le plus beau.',
            'type' => 'vrai_faux',
            'reponses' => [
                ['Vrai', true],
                ['Faux', false],
            ],
        ],
        [
            'enonce' => 'Nous allons échouer la session.',
            'type' => 'vrai_faux',
            'reponses' => [
                ['Vrai', false],
                ['Faux', true],
            ],
        ],
    ];

    // 4. Transaction : soit tout est inséré, soit rien (pas de quiz à moitié créé)
    $pdo->beginTransaction();
    try {
        // Insérer le quiz lui-même
        $stmtQuiz = $pdo->prepare("INSERT INTO quiz (titre, description, user_id) VALUES (?, ?, ?)");
        $stmtQuiz->execute([
            $titreTest,
            'Un petit quiz pour tester l\'application.',
            $farid['id'],
        ]);
        $quizId = $pdo->lastInsertId();   // id du quiz qu'on vient de créer, nécessaire pour lier les questions

        // Préparer les requêtes une seule fois, puis les réutiliser dans la boucle
        $stmtQuestion = $pdo->prepare("INSERT INTO questions (enonce, type, quiz_id) VALUES (?, ?, ?)");
        $stmtReponse = $pdo->prepare("INSERT INTO reponses (texte, est_correcte, question_id) VALUES (?, ?, ?)");

        // Pour chaque question du tableau...
        foreach ($quizData as $q) {
            $stmtQuestion->execute([$q['enonce'], $q['type'], $quizId]);
            $questionId = $pdo->lastInsertId();   // id de la question, nécessaire pour lier ses réponses

            // ...insérer ses réponses
            // [$texte, $estCorrecte] extrait directement les deux valeurs de chaque paire
            foreach ($q['reponses'] as [$texte, $estCorrecte]) {
                // (int) convertit true/false en 1/0 pour la colonne BOOL
                $stmtReponse->execute([$texte, (int) $estCorrecte, $questionId]);
            }
        }
        // Tout s'est bien passé : on valide les insertions
        $pdo->commit();
    } catch (Exception $e) {
        // En cas d'erreur : on annule tout ce qui a été inséré dans la transaction
        $pdo->rollBack();
        throw $e;   // on relance l'erreur pour la voir au lieu de l'avaler en silence
    }
}