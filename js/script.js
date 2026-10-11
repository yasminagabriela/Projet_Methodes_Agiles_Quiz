/* Dark/Light mode */
const boutonTheme = document.getElementById("theme-toggle");
const imageTheme = boutonTheme.querySelector("img");

boutonTheme.addEventListener("click", function() {
    document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            imageTheme.src = "images/light-mode.png";
        } else {
            imageTheme.src = "images/dark-mode.png";
        }
    });

/* Choix de role etudiant/enseignant */
const etudiant = document.getElementById("etudiant-label");
const enseignant = document.getElementById("enseignant-label");

    etudiant.addEventListener("click", function() {
        etudiant.classList.add("selected");
        enseignant.classList.remove("selected");
    });

    enseignant.addEventListener("click", function() {
        enseignant.classList.add("selected");
        etudiant.classList.remove("selected");
    });
