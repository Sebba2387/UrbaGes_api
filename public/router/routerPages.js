import { loadComponent } from './loader.js';

const routes = [
    { path: '/', component: 'home.html' },  // Page de connexion
    { path: '/profil', component: 'profil.html' },
    { path: '/monEquipe', component: 'monEquipe.html' },
    { path: '/editProfil', component: 'editProfil.html' },
    { path: '/editPassword', component: 'editPassword.html' },
    { path: '/statistiques', component: 'statistiques.html' },
    { path: '/dossiers', component: 'dossiers.html' },
    { path: '/editDossier', component: 'editDossier.html' },
    { path: '/attributions', component: 'attributions.html' },
    { path: '/gep', component: 'gep.html' },
    { path: '/plu', component: 'plu.html' },
    { path: '/editPlu', component: 'editPlu.html' },
    { path: '/communes', component: 'communes.html' },
    { path: '/editCommune', component: 'editCommune.html' },
    { path: '/addCommune', component: 'addCommune.html' },
    { path: '/courriers', component: 'courriers.html' },
    { path: '/editCourrier', component: 'editCourrier.html' },
    { path: '/verif', component: 'verif.php' },
];

// Flag pour savoir si le header et la sidebar sont déjà chargés
let componentsLoaded = false;

// Fonction asynchrone pour charger dynamiquement le header et la sidebar
async function loadHeaderAndSidebar() {
    if (componentsLoaded) return;  // Ne charger que si ce n'est pas déjà fait

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
        componentsLoaded = true;  // Marquer que les composants sont chargés

    } catch (error) {
        console.error('Erreur de chargement du header ou de la sidebar:', error);
    }

    // Ajoute le comportement des liens de navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const path = link.getAttribute('href');
            changePage(path);
        });
    });

    // Appeler ici pour changer l'image / pseudo de profil
    const userId = localStorage.getItem('userId');
    if (userId) {
        fetch('http://localhost:8080/public/api/userApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getProfile', userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'image de profil en fonction du genre
                updateProfileImage(data.user.genre);
    
                // Mettre à jour le pseudo dans la sidebar
                const pseudoElement = document.getElementById('sidebar-pseudo');
                if (pseudoElement && data.user.pseudo) {
                    pseudoElement.textContent = data.user.pseudo;
                }
            }
        })
        .catch(err => console.error("Erreur lors du chargement du genre utilisateur :", err));
    }
}

// Fonction de routage principale
async function router() {
    const path = window.location.pathname;
    const route = routes.find(route => route.path === path) || routes[0];

    // Si on est sur la page de connexion, ne pas charger header et sidebar
    if (route.path !== '/') {
        await loadHeaderAndSidebar();  // Charger le header et la sidebar si ce n'est pas déjà fait
    }

    // Charger le composant de la page en fonction de la route
    await loadComponent(route.component);

    // Si la route nécessite des utilisateurs (par exemple '/monEquipe')
    if (route.path === '/monEquipe') {
        // Assurer que les utilisateurs sont chargés après l'injection du composant
        if (typeof window.fetchAllUsers === 'function') {
            window.fetchAllUsers();
        }
    }

    // Ajouter la classe 'active' au lien correspondant
    updateActiveLink(route.path);
}

// Fonction pour gérer les changements d'URL sans rechargement de la page
function changePage(path) {
    window.history.pushState({}, '', path);  // Modifier l'URL sans recharger la page
    router();  // Recharger le contenu et mettre à jour la page
}

// Ajouter un event listener pour intercepter les changements d'URL
window.addEventListener('popstate', () => {
    router();  // Recharger la page en fonction de la nouvelle URL
});

// Exécuter le router une fois que le contenu de la page est chargé
window.addEventListener('DOMContentLoaded', router);

// Ajouter un event listener sur les liens pour éviter le rechargement de la page
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();  // Empêcher le rechargement de la page
        const path = link.getAttribute('href');
        changePage(path);  // Changer de page sans recharger
    });
});

window.changePage = changePage;