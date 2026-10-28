<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$titreRole = (($_SESSION['role'] ?? '') === 'enseignant') ? 'Professeur' : 'Étudiant(e)';
$title = trim($_POST['title'] ?? '');
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
                <span>0 question</span>
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
                <input type="text" id="title" name="title"
                    value="<?= htmlspecialchars($title ?? '') ?>">

                <label for="description">Description (facultatif)</label>
                <textarea id="description" name="description"></textarea>

                <label>
                    <input type="checkbox" name="correction" checked>
                    Montrer la correction à l'étudiant après la soumission
                </label>

                <button type="submit">Enregistrer</button>
            </form>
        </details>

        <!-- Onglets -->
        <div class="quiz-tabs">
            <button type="button" class="active" id="tab-questions">Questions (0)</button>
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

            <div class="questions-empty">
                <img src="images/mascot.png" alt="Mascotte Qivvi" class="quiz-mascotte">
                <h3>Aucune question pour le moment</h3>
                <p>Ajoutez votre première question pour construire le quiz.</p>
            </div>

            <!-- Les questions ajoutées apparaissent ici -->
            <div id="questions-list"></div>

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

            <!-- Modèle d'une réponse : utilisé par le JS -->
            <template id="reponse-template">
                <div class="reponse-row">
                    <input type="checkbox" class="r-correcte" title="Bonne réponse">
                    <input type="text" class="r-texte" placeholder="Réponse">
                    <button type="button" class="r-supprimer" title="Supprimer la réponse">✕</button>
                </div>
            </template>

            <button type="button" class="add-question">+ Ajouter une question</button>

        </section>
        </main>



    <script src="js/script.js?v=3"></script>
</body>

</html>