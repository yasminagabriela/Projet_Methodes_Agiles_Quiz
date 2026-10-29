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
        alert(result.erreur);
        return null;
    }
    return result.id;
}

/* Ajouter une question (page quiz.php) */
const boutonAjouterQuestion = document.querySelector(".add-question");
const listeQuestions = document.getElementById("questions-list");
const templateQuestion = document.getElementById("question-template");
const templateReponse = document.getElementById("reponse-template");

// Les questions ajoutées, gardées en mémoire jusqu'au clic sur "Enregistrer le quiz"
const questionsQuiz = [];

// Redessine toutes les questions avec le modèle (template) + met à jour les compteurs
function afficherQuestions() {
    listeQuestions.innerHTML = "";

    questionsQuiz.forEach(function (q, i) {
        // Copier le modèle de question
        const carte = templateQuestion.content.cloneNode(true);

        carte.querySelector(".q-numero").textContent = i + 1;
        carte.querySelector(".q-enonce").textContent = q.enonce;
        carte.querySelector(".q-type").textContent =
            q.type === "choix_multiple" ? "QCM" : "Vrai/Faux";

        const conteneurReponses = carte.querySelector(".q-reponses");

        q.reponses.forEach(function (r, index) {
            const ligne = document.createElement("div");
            ligne.className = "reponse-row";
            if (r.est_correcte) {
                ligne.classList.add("correcte");
            }

            const lettre = document.createElement("span");
            lettre.className = "reponse-lettre";
            lettre.textContent = String.fromCharCode(65 + index);

            const contenu = document.createElement("span");
            contenu.textContent = r.texte;

            ligne.appendChild(lettre);
            ligne.appendChild(contenu);

            if (r.est_correcte) {
                const indication = document.createElement("span");
                indication.className = "bonne-reponse";
                indication.textContent = "✓ Bonne réponse";
                ligne.appendChild(indication);
            }

            conteneurReponses.appendChild(ligne);
        });

        listeQuestions.appendChild(carte);
    });

    const n = questionsQuiz.length;
    const panneauOuvert = !document.getElementById("new-question-panel").hidden;

    document.getElementById("tab-questions").textContent = "Questions (" + n + ")";
    document.querySelector(".quiz-badges span:nth-child(2)").textContent =
        n + (n > 1 ? " questions" : " question");
    document.querySelector(".questions-empty").hidden = n > 0 || panneauOuvert;
}

// Clic sur "Ajouter la question" (validation + mémorisation + affichage)
const boutonSauvegarderQuestion = document.getElementById("new-save-question");

if (boutonSauvegarderQuestion && listeQuestions) {
    boutonSauvegarderQuestion.addEventListener("click", function () {
        const enonce = document.getElementById("new-question-text").value.trim();
        const type = document.querySelector(".question-types button.selected").dataset.type;

        if (enonce === "") {
            alert("Veuillez écrire une question.");
            return;
        }

        // Récupérer les réponses du formulaire
        const reponses = [];
        document.querySelectorAll("#new-question-answers .new-answer-row").forEach(function (ligne) {
            reponses.push({
                texte: ligne.querySelector(".new-answer-text").value.trim(),
                est_correcte: ligne.querySelector("input[type='radio']").checked,
            });
        });

        if (reponses.some(function (r) { return r.texte === ""; })) {
            alert("Veuillez remplir toutes les réponses.");
            return;
        }

        // Mémoriser la question (c'est ce tableau qui sera envoyé à api.php)
        questionsQuiz.push({ enonce: enonce, type: type, reponses: reponses });

        // Remettre le formulaire à zéro et le fermer
        document.getElementById("new-question-text").value = "";
        document.querySelector('.question-types button[data-type="choix_multiple"]').click();
        document.getElementById("new-question-panel").hidden = true;
        boutonAjouterQuestion.hidden = false;

        // Dessiner la liste (après avoir fermé le formulaire)
        afficherQuestions();
    });
}

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
    const formulaireQuestion = document.getElementById("new-question-panel");
    const boutonAnnuler = document.getElementById("new-cancel-question");



    const messageVide = document.querySelector(".questions-empty");

    boutonAjouterQuestion.addEventListener("click", function () {
        formulaireQuestion.hidden = false;
        messageVide.hidden = true;
        boutonAjouterQuestion.hidden = true;

        document.getElementById("new-question-text").focus();
    });

    boutonAnnuler.addEventListener("click", function () {
        formulaireQuestion.hidden = true;
        boutonAjouterQuestion.hidden = false;

        if (listeQuestions.querySelectorAll(".question-block").length === 0) {
            messageVide.hidden = false;
        }
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
            const index = Array.from(listeQuestions.children).indexOf(bloc);
            questionsQuiz.splice(index, 1);
            afficherQuestions();
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

/* Switch entre les deux sections "Resultat" et "Questions" */
const tabQuestions = document.getElementById("tab-questions");
const tabResults = document.getElementById("tab-results");

const sectionQuestions = document.querySelector(".quiz-questions");
const sectionResults = document.querySelector(".quiz-results");

if (tabQuestions && tabResults && sectionQuestions && sectionResults) {

    tabResults.addEventListener("click", function () {
        sectionQuestions.hidden = true;
        sectionResults.hidden = false;

        tabQuestions.classList.remove("active");
        tabResults.classList.add("active");
    });
    tabQuestions.addEventListener("click", function () {
        sectionQuestions.hidden = false;
        sectionResults.hidden = true;

        tabResults.classList.remove("active");
        tabQuestions.classList.add("active");
    });
}

/* crée une ligne de réponse avec une lettre (A, B, C…) */
const reponsesQuestion = document.getElementById("new-question-answers");

function creerReponse(texte = "", numero = 0, vraiFaux = false) {
    const ligne = document.createElement("div");
    ligne.className = "new-answer-row";

    const lettre = String.fromCharCode(65 + numero);

    ligne.innerHTML = `
        <span class="new-answer-letter">${lettre}</span>
        <input type="text" class="new-answer-text" placeholder="Réponse ${numero + 1}">
        <label class="new-answer-correct">
            <input type="radio" name="new-correct-answer">
            Bonne réponse
        </label>
    `;
    const champ = ligne.querySelector(".new-answer-text");
    champ.value = texte;

    if (vraiFaux) {
        champ.readOnly = true;
    }
    if (numero === 0) {
        ligne.querySelector("input[type='radio']").checked = true;
    }
    reponsesQuestion.appendChild(ligne);
}
if (reponsesQuestion) {
    creerReponse("", 0);
    creerReponse("", 1);
}

/* Ajouter une reponse  */
const boutonAjouterReponse = document.getElementById("new-add-answer");

if (boutonAjouterReponse && reponsesQuestion) {
    boutonAjouterReponse.addEventListener("click", function () {
        const nombre = reponsesQuestion.children.length;

        if (nombre < 5) {
            creerReponse("", nombre);
        }
        if (nombre + 1 >= 5) {
            boutonAjouterReponse.hidden = true;
        }
    });
}

/* Choisir le type de question */
const boutonsTypes = document.querySelectorAll(".question-types button");
const instructionsQuestion = document.getElementById("new-question-instructions");

if (reponsesQuestion && boutonAjouterReponse && instructionsQuestion) {
    boutonsTypes.forEach(function (bouton) {
        bouton.addEventListener("click", function () {

            boutonsTypes.forEach(function (b) {
                b.classList.remove("selected");
            });
            bouton.classList.add("selected");
            reponsesQuestion.innerHTML = "";

            if (bouton.dataset.type === "vrai_faux") {
                creerReponse("Vrai", 0, true);
                creerReponse("Faux", 1, true);

                instructionsQuestion.textContent = "Choisissez la bonne réponse.";
                boutonAjouterReponse.hidden = true;
            } else {
                creerReponse("", 0);
                creerReponse("", 1);

                instructionsQuestion.textContent =
                    "Entrez 2 à 5 réponses possibles et choisissez la bonne.";
                boutonAjouterReponse.hidden = false;
            }
        });
    });
}

/* Enregistrer le quiz */
const boutonSauvegarderQuiz = document.getElementById("save-quiz");

if (boutonSauvegarderQuiz) {
    boutonSauvegarderQuiz.addEventListener("click", async function () {
        const quiz = {
            titre: document.getElementById("title").value.trim(),
            description: document.getElementById("description").value.trim(),
            questions: questionsQuiz,
        };

        boutonSauvegarderQuiz.disabled = true;   // évite de créer le quiz deux fois

        try {
            const id = await envoyerQuiz(quiz);
            if (id !== null) {
                window.location.href = "index.php";
                return;
            }
        } catch (err) {
            alert("Erreur de connexion avec le serveur.");
        }

        boutonSauvegarderQuiz.disabled = false;
    });
}
