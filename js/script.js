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
