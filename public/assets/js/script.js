// Fonction pour activer la mise en surbrillance de l'élément sélectionné
function setActive(element) {
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    element.classList.add('active');
}

// Fonction pour mettre à jour l'état actif des liens de navigation
function updateActiveLink(path) {
    // Récupérer tous les liens de navigation
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Retirer la classe 'active' de tous les liens
    navLinks.forEach(link => {
        link.classList.remove('active');
    });

    // Ajouter la classe 'active' au lien correspondant à la route
    const activeLink = [...navLinks].find(link => link.getAttribute('href') === path);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Fonction pour initialiser le toggle de la sidebar
function setupSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

    if (sidebar && toggleBtn && toggleIcon) {
        // Fermer la sidebar au début
        sidebar.classList.add('collapsed');
        toggleIcon.classList.add('bi-chevron-right');

        // Ajouter l'événement de clic sur le bouton de toggle
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');  
            
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            } else {
                toggleIcon.classList.remove('bi-chevron-right');
                toggleIcon.classList.add('bi-chevron-left');
            }
        });
    }
}

// Fonction pour rediriger dynamiquement selon la connexion
function updateProfileLink() {
    const profileLink = document.querySelector(".navbar-brand");

    if (profileLink) {
        const userId = localStorage.getItem("id_utilisateur");
        profileLink.setAttribute("href", userId ? "/profil" : "/");
    }
}
// Appelle la fonction dès que le script est chargé
document.addEventListener("DOMContentLoaded", updateProfileLink);

// Fonction pour afficher les formulaires
function toggleForm(formId, button) {
    let form = document.getElementById(formId);
    form.classList.toggle("expanded");

    if (form.classList.contains("expanded")) {
        button.classList.remove("text-light");
        button.classList.add("text-primary");
    } else {
        button.classList.remove("text-primary");
        button.classList.add("text-light");
    }
}

// Fonction pour copier les courriers dynamiques
function copierCourrier(event) {
    event.preventDefault(); // Empêche la soumission du formulaire

    let input = document.querySelector("textarea[name='corps_courrier_gen']");
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value)
            .catch(err => console.error("Erreur de copie :", err));
    }
}

// Fonction pour copier le contenu d'un champs
function copierTexte(event, inputName) {
    event.preventDefault(); // Empêche la soumission du formulaire

    let input = document.querySelector(`input[name='${inputName}']`);
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value) // Copie le texte
            .catch(err => console.error("Erreur de copie :", err));
    }
}

// Fonction pour afficher dynamiquement la photo de profil selon le genre
function updateProfileImage(genre) {
    const photoProfil = document.querySelector(".photo-profil img");
    if (photoProfil) {
        const genreLower = genre.toLowerCase();
        const imageUrl = genreLower === "femme"
            ? "/public/assets/images/img_profil_femme.jpg"
            : "/public/assets/images/img_profil_homme.jpg";
        if (photoProfil.src.endsWith(imageUrl)) {
            photoProfil.style.display = "block"; // L’image est déjà bonne, juste l’afficher
            return;
        }
        const tempImg = new Image();
        tempImg.onload = () => {
            photoProfil.src = imageUrl;
            photoProfil.style.display = "block";
        };
        tempImg.src = imageUrl;
    }
}

// Fonction pour réinitialiser les recherches
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form && typeof form.reset === "function") {
        form.reset();

        const callback = form.getAttribute("data-callback");
        if (callback && typeof window[callback] === "function") {
            window[callback](new FormData(form));
        }
    } else {
        console.warn(`L'élément avec l'ID '${formId}' n'est pas un formulaire valide.`);
    }
}

