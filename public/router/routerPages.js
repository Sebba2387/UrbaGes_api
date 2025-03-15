import { loadComponent } from './loader.js';

const routes = [
    { path: '/', component: 'home.html' },  // Page de connexion
    { path: '/profil', component: 'profil.html' },
];

// Fonction asynchrone pour charger dynamiquement le header et la sidebar
async function loadHeaderAndSidebar() {
    const header = document.getElementById('header');
    const sidebar = document.getElementById('sidebar-container');
    
    try {
        // Charger dynamiquement le header
        const headerResponse = await fetch('/public/components/header.html');
        const headerHtml = await headerResponse.text();
        header.innerHTML = headerHtml;

        // Charger dynamiquement la sidebar
        const sidebarResponse = await fetch('/public/components/sidebar.html');
        const sidebarHtml = await sidebarResponse.text();
        sidebar.innerHTML = sidebarHtml;
        
        // Initialiser le toggle de la sidebar après son chargement
        setupSidebarToggle();  
    } catch (error) {
        console.error('Erreur de chargement du header ou de la sidebar:', error);
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

// Fonction de routage principale
async function router() {
    const path = window.location.pathname;
    const route = routes.find(route => route.path === path) || routes[0];

    // Si on est sur la page de connexion, ne pas charger header et sidebar
    if (route.path !== '/') {
        await loadHeaderAndSidebar();  // Charger le header et la sidebar
    }

    // Charger le composant de la page en fonction de la route
    await loadComponent(route.component);  
}

// Exécuter le router une fois que le contenu de la page est chargé
window.addEventListener('DOMContentLoaded', router);
