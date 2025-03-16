export function loadComponent(component) {
    const mainPage = document.getElementById('main-page');
    fetch(`/public/pages/${component}`)
        .then(response => response.text())
        .then(html => {
            mainPage.innerHTML = html;  // Remplacer uniquement le contenu principal
        })
        .catch(error => {
            console.error('Erreur lors du chargement du composant:', error);
        });
}


