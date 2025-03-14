import { loadComponent } from './loader.js';

const routes = [
    { path: '/', component: 'home.html' },  // Page de connexion
    { path: '/profil', component: 'profil.html' },
];

function loadHeaderAndSidebar() {
    const header = document.getElementById('header');
    const sidebar = document.getElementById('sidebar-container');
    
    // Charger dynamiquement le header et la sidebar
    fetch('/public/components/header.html')
        .then(response => response.text())
        .then(html => {
            header.innerHTML = html;
        })
        .catch(error => console.error('Erreur de chargement du header:', error));

    fetch('/public/components/sidebar.html')
        .then(response => response.text())
        .then(html => {
            sidebar.innerHTML = html;
            setupSidebarToggle();  // Initialiser le toggle de la sidebar après son chargement
        })
        .catch(error => console.error('Erreur de chargement de la sidebar:', error));
}

function setupSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

    // Toggle de la sidebar
    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');  // Toggle la classe qui réduit/agrandit la sidebar
        toggleIcon.classList.toggle('bi-chevron-left');  // Changer de direction du chevron
        toggleIcon.classList.toggle('bi-chevron-right');
    });
}

function router() {
    const path = window.location.pathname;
    const route = routes.find(route => route.path === path) || routes[0];

    // Si on est sur la page de connexion, ne pas charger header et sidebar
    if (route.path !== '/') {
        loadHeaderAndSidebar();  // Charger le header et la sidebar
    }

    loadComponent(route.component);  // Charger le contenu de la page
}

// Exécutez le router une fois que le contenu de la page est chargé
window.addEventListener('DOMContentLoaded', router);
