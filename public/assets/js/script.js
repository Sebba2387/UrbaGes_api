// Fonction pour activer la mise en surbrillance de l'élément sélectionné
function setActive(element) {
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    element.classList.add('active');
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
