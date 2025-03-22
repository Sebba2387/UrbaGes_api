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
        // Ajouter l'événement de clic sur le bouton de toggle
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');  // Toggle la classe qui réduit/agrandit la sidebar
            toggleIcon.classList.toggle('bi-chevron-left');  // Changer de direction du chevron
            toggleIcon.classList.toggle('bi-chevron-right');
        });
    }
}

// Fonction pour afficher les formulaires
function toggleForm(formId) {
    let form = document.getElementById(formId);
    form.classList.toggle("expanded");
}

// Fonction pour copier les liens des dossiers
function copierTexte(event) {
    event.preventDefault(); // Empêche la soumission du formulaire

    let input = document.querySelector("input[name='lien_courrier']");
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value) // Copie le texte
            .catch(err => console.error("Erreur de copie :", err));
    }
}

// Fonction pour copier les liens
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