// Fonction pour activer la mise en surbrillance de l'élément sélectionné
function setActive(element) {
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    element.classList.add('active');
}
