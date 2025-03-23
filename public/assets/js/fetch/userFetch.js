// Connexion utilisateur
function loginUser(email, password) {
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer your_token_here'  // Ajouter le token pour l'authentification
        },
        body: JSON.stringify({
            action: 'login',
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('userId', data.user.id_utilisateur);
            window.location.href = "http://localhost/public/testPages/testProfil.html";
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur:', error));
}


// Récupération du profil utilisateur

function fetchUserProfile() {
    const userId = localStorage.getItem('userId');
    if (!userId) {
        window.location.href = "http://localhost/public/testPages/testLogin.html";
        return;
    }

    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer your_token_here'  // Ajouter le token pour l'authentification
        },
        body: JSON.stringify({
            action: 'getProfile',
            userId: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Utilisation de setTimeout pour s'assurer que le DOM est prêt
            setTimeout(() => {
                const prenomInput = document.getElementById("prenom");
                const nomInput = document.getElementById("nom");
                const emailInput = document.getElementById("email");
                const anneeNaissanceInput = document.getElementById("anneeNaissance");
                const genreInput = document.getElementById("genre");
                const pseudoInput = document.getElementById("pseudo");
                const posteInput = document.getElementById("poste");
                const roleInput = document.getElementById("nom_role");

                if (prenomInput && nomInput && emailInput && anneeNaissanceInput 
                    && genreInput && pseudoInput && posteInput && roleInput) {
                    prenomInput.value = data.user.prenom;
                    nomInput.value = data.user.nom;
                    emailInput.value = data.user.email;
                    anneeNaissanceInput.value = data.user.annee_naissance;
                    genreInput.value = data.user.genre;
                    pseudoInput.value = data.user.pseudo;
                    posteInput.value = data.user.poste;
                    roleInput.value = data.user.nom_role;
                    console.log('Formulaire mis à jour');
                } else {
                    console.error('Un ou plusieurs champs du formulaire sont manquants');
                }
            }, 100);  // Attendre 100ms pour s'assurer que le DOM est prêt
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la récupération du profil utilisateur:', error);
    });
}


// Déconnexion utilisateur via le backend
function logoutUser() {
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('userId'); // Supprimer l'ID utilisateur du localStorage
            window.location.href = "http://localhost/public/testPages/testLogin.html"; // Rediriger vers la page de connexion
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur de déconnexion :', error));
}
// Affichage des utilisateurs dans le tableau HTML
function displayUsersTable(users) {
    const tableBody = document.getElementById("usersTableBody");
    // Vérifie si l'élément existe avant d'essayer de manipuler son contenu
    if (!tableBody) {
        console.error('L\'élément avec l\'ID "usersTableBody" est introuvable.');
        return; // Sort de la fonction si l'élément n'existe pas
    }
    tableBody.innerHTML = ""; // Vider le tableau avant de le remplir

    users.forEach(user => {
        let row = `<tr>
            <td>${user.id_utilisateur}</td>
            <td>${user.nom}</td>
            <td>${user.prenom}</td>
            <td>${user.email}</td>
            <td>${user.annee_naissance}</td>
            <td>${user.pseudo}</td>
            <td>${user.genre}</td>
            <td>${user.poste}</td>
            <td>${user.nom_role}</td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

function fetchAllUsers() {
    const tableBody = document.getElementById("usersTableBody");

    // Vérifie si l'élément est bien présent dans le DOM avant de faire l'appel API
    if (!tableBody) {
        console.log('Bienvenue dans UrbaGes');
        return;
    }
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer your_token_here'
        },
        body: JSON.stringify({
            action: 'getAllUsers'
        })
    })
    .then(response => response.text()) // <-- Change response.json() en response.text()
    .then(text => {
        return JSON.parse(text);
    })
    .then(data => {
        if (data.success) {
            displayUsersTable(data.users);
        } else {
            alert("Erreur lors de la récupération des utilisateurs");
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// Attendre que le DOM soit entièrement chargé avant d'exécuter la logique
document.addEventListener("DOMContentLoaded", function() {
    // Appelle la fonction pour récupérer et afficher les utilisateurs après que la page soit prête
    fetchAllUsers();
});
