
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quivvi</title><title>Qivvi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="images/icon.png">
</head>
    <body>
        <!-- HEADER -->
        <?php include __DIR__ . '/includes/header.php'; ?>

        <!-- Les details du quiz -->
        <main class="quiz-details">
            <button class="">Tous mes quiz</button>
            
            <!-- La section pour la publication du quiz -->
            <section class="quiz-status">
                <?php $title = trim($_POST['title'])?>
                <p>Publiez le quiz pour que vos étudiants puissent y accéder avec un code.</p>
                <button>Publier le quiz</button>
            </section>

            <!-- Ouvrir et de fermer la section pour modifier le titre et les options -->
            <details class="quiz-options">
                <summary>Modifier le titre et les options</summary>

                <form methode="post">
                    <label for="title">Titre</label>
                    <input type="text" name="title">

                    <label>Description (facultatif)</label>
                    <input type="text" name="description">
                    
                    <label>
                        <input type="checkbox" name="correction" checked>
                        Montrer la correction à l'étudiant après la soumission
                    </label>

                    <button type="submit">Enregistrer</button>
                </form>
            </details>

            <!-- Section de nombre des questions -->
            <section class="quiz-questions">
                <h1>Questions<h1>
                <p>Ce quiz n'a pas encore de question.</p>
                <button type="button">Ajouter une question</button>
            </section>


        </main>


    <script src="js/script.js"></script>
    </body>
</html>