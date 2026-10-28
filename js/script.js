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

const boutonSauvegarder = document.getElementById("new-save-question");

if (boutonSauvegarder) {
    boutonSauvegarder.addEventListener("click", function () {
        const enonce = document.getElementById("new-question-text").value.trim();
        const type = document.querySelector(".question-types button.selected").dataset.type;

        if (enonce === "") {
            alert("Veuillez écrire une question.");
            return;
        }
        const champsReponses = document.querySelectorAll("#new-question-answers .new-answer-text");
        for (const champ of champsReponses) {
            if (champ.value.trim() === "") {
                alert("Veuillez remplir toutes les réponses.");
                return;
            }
        }
        console.log(enonce);
        console.log(type);

        // Copier le modèle de question
        const question = templateQuestion.content.cloneNode(true);
        // Ajouter l'énoncé
        question.querySelector(".q-enonce").textContent = enonce;
        // Ajouter le type
        question.querySelector(".q-type").textContent =
            type === "choix_multiple" ? "QCM" : "Vrai/Faux";

        // Récupérer les réponses du formulaire
        const reponses = document.querySelectorAll("#new-question-answers .new-answer-row");
        const conteneurReponses = question.querySelector(".q-reponses");

        reponses.forEach(function (reponse, index) {
            const texte = reponse.querySelector(".new-answer-text").value;
            const correcte = reponse.querySelector("input[type='radio']").checked;

            const ligne = document.createElement("div");
            ligne.className = "reponse-row";

            if (correcte) {
                ligne.classList.add("correcte");
            }

            const lettre = document.createElement("span");
            lettre.className = "reponse-lettre";
            lettre.textContent = String.fromCharCode(65 + index);

            const contenu = document.createElement("span");
            contenu.textContent = texte;

            ligne.appendChild(lettre);
            ligne.appendChild(contenu);

            if (correcte) {
                const indication = document.createElement("span");
                indication.className = "bonne-reponse";
                indication.textContent = "✓ Bonne réponse";
                ligne.appendChild(indication);
            }

            conteneurReponses.appendChild(ligne);
        });
        // Afficher la question dans la liste
        listeQuestions.appendChild(question);

        // Actualiser les compteurs
        mettreAJourCompteurs();

        // Fermer le formulaire
        document.getElementById("new-question-panel").hidden = true;
        boutonAjouterQuestion.hidden = false;
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