/* Dark/Light mode */
const boutonTheme = document.getElementById("theme-toggle");
const imageTheme = boutonTheme.querySelector("img");


// Appliquer le thème
function appliquerTheme(sombre) {
    document.body.classList.toggle("dark-mode", sombre);
    imageTheme.src = sombre ? "images/light-mode.png" : "images/dark-mode.png";
}

// Au charement : reprendre le thème sauvegardé
appliquerTheme(localStorage.getItem("theme") === "dark");


// Au clic : changer le thème et le sauvegarder
boutonTheme.addEventListener("click", function () {
    const sombre = !document.body.classList.contains("dark-mode");
    appliquerTheme(sombre);
    localStorage.setItem("theme", sombre ? "dark" : "light");
});

/* Choix de role etudiant/enseignant sur la page de login */
const etudiant = document.getElementById("etudiant-label");
const enseignant = document.getElementById("enseignant-label");
const error = document.querySelector(".error-text");

if (etudiant && enseignant) {
    etudiant.addEventListener("click", function () {
        etudiant.classList.add("selected");
        enseignant.classList.remove("selected");
        // Enlever le text "Erreur de validation" quand on switch de role
        if (error) error.style.display = "none";
    });

    enseignant.addEventListener("click", function () {
        enseignant.classList.add("selected");
        etudiant.classList.remove("selected");
        if (error) error.style.display = "none";
    });
}

/* calling the api */

async function envoyerQuiz(quiz) {
    const res = await fetch('includes/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(quiz),
    });
    const result = await res.json();
    if (!res.ok) {
        console.error(result.erreur);
        return null;
    }
    return result.id;
}

/* Ajouter une question (page quiz.php) */
const boutonAjouterQuestion = document.querySelector(".add-question");
const listeQuestions = document.getElementById("questions-list");
const templateQuestion = document.getElementById("question-template");
const templateReponse = document.getElementById("reponse-template");

// Ajouter une ligne de réponse (max 5)
function ajouterReponse(bloc, texte = "") {
    const reponses = bloc.querySelector(".q-reponses");
    if (reponses.children.length >= 5) return;
    const ligne = templateReponse.content.cloneNode(true);
    ligne.querySelector(".r-texte").value = texte;
    reponses.appendChild(ligne);
}

// Mettre à jour les numéros, "Questions (N)", le badge et le message vide
function mettreAJourCompteurs() {
    const blocs = listeQuestions.querySelectorAll(".question-block");
    blocs.forEach(function (bloc, i) {
        bloc.querySelector(".q-numero").textContent = i + 1;
    });
    const n = blocs.length;
    document.querySelector(".quiz-tabs .active").textContent = "Questions (" + n + ")";
    document.querySelector(".quiz-badges span:nth-child(2)").textContent = n + (n > 1 ? " questions" : " question");
    document.querySelector(".questions-empty").style.display = n === 0 ? "" : "none";
}

if (boutonAjouterQuestion && listeQuestions) {
    // Au clic : copier le modèle avec 2 réponses vides
    boutonAjouterQuestion.addEventListener("click", function () {
        const fragment = templateQuestion.content.cloneNode(true);
        const bloc = fragment.querySelector(".question-block");
        ajouterReponse(bloc);
        ajouterReponse(bloc);
        listeQuestions.appendChild(fragment);
        mettreAJourCompteurs();
        bloc.querySelector(".q-enonce").focus();
    });

    // Un seul écouteur pour les boutons de toutes les questions, même celles ajoutées plus tard
    listeQuestions.addEventListener("click", function (e) {
        const bloc = e.target.closest(".question-block");
        if (!bloc) return;

        if (e.target.classList.contains("q-ajouter-reponse")) {
            ajouterReponse(bloc);
        } else if (e.target.classList.contains("r-supprimer")) {
            // Garder au moins 2 réponses
            if (bloc.querySelectorAll(".reponse-row").length > 2) {
                e.target.closest(".reponse-row").remove();
            }
        } else if (e.target.classList.contains("q-supprimer")) {
            bloc.remove();
            mettreAJourCompteurs();
        }
    });

    // Vrai/Faux : remplacer les réponses par "Vrai" et "Faux"
    listeQuestions.addEventListener("change", function (e) {
        if (!e.target.classList.contains("q-type")) return;
        const bloc = e.target.closest(".question-block");
        const vraiFaux = e.target.value === "vrai_faux";

        bloc.querySelector(".q-reponses").innerHTML = "";
        ajouterReponse(bloc, vraiFaux ? "Vrai" : "");
        ajouterReponse(bloc, vraiFaux ? "Faux" : "");
        bloc.querySelector(".q-ajouter-reponse").hidden = vraiFaux;
    });
}
