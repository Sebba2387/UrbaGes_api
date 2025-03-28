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
            <td>
                <button onclick="redirectToEdit(${user.id_utilisateur})">Modifier</button>
                <button onclick="deleteUser(${user.id_utilisateur})">Supprimer</button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

// Fonction pour récupérer et afficher les utilisateurs
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
        },
        body: JSON.stringify({
            action: 'getAllUsers'
        })
    })
    .then(response => response.json())  // On attend une réponse JSON
    .then(data => {
        if (data.success) {
            if (data.userRole === 'admin' || data.userRole === 'moderateur') {
                displayUsersTable(data.users);  // Affichage des utilisateurs
                tableBody.style.display = 'table-row-group';  // Rendre visible la table
            } else {
                // Si l'utilisateur n'a pas le bon rôle, masquer la table et éventuellement afficher un message
                tableBody.style.display = 'none';  // Cacher la table des utilisateurs
                // Optionnel : afficher un message d'erreur si tu veux
                const message = document.createElement('p');
                message.textContent = 'Vous n\'avez pas les permissions nécessaires pour voir cette page.';
                document.body.appendChild(message);
            }
        } else {
            alert(data.message);  // Afficher le message d'erreur
            tableBody.style.display = 'none';  // Cacher la table en cas d'erreur
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// Inscription d'un nouveau utilisateur
document.addEventListener("DOMContentLoaded", function() {
    fetchAllUsers();
    const addUserButton = document.getElementById("addUserButton");
    if (addUserButton) {
        addUserButton.addEventListener("click", function() {
            window.location.href = "http://localhost/public/testPages/testRegister.html";
        });
    }
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function(event) {
            event.preventDefault();

            fetch('http://localhost/public/api/userApi.php', {
                method: 'POST',
                credentials: 'include', // Permet d'envoyer les cookies de session
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'registerUser',
                    nom: document.getElementById("nom").value,
                    prenom: document.getElementById("prenom").value,
                    email: document.getElementById("email").value,
                    password: document.getElementById("password").value,
                    annee_naissance: document.getElementById("annee_naissance").value,
                    pseudo: document.getElementById("pseudo").value,
                    genre: document.getElementById("genre").value,
                    poste: document.getElementById("poste").value
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) window.location.href = "http://localhost/public/testPages/testProfil.html";
            })
            .catch(error => console.error('Erreur:', error));
        });
    }
});

// Redirection vers l'édition du profil
function redirectToEdit(userId) {
    window.location.href = `http://localhost/public/testPages/testEditProfil.html?id=${userId}`;
}
// Mise à jour du profil utilisateur
function updateUserProfile() {
    const userId = document.getElementById("user_id").value;
    const userData = {
        action: 'updateUser',
        id_utilisateur: userId,
        nom: document.getElementById("nom").value,
        prenom: document.getElementById("prenom").value,
        email: document.getElementById("email").value,
        annee_naissance: document.getElementById("annee_naissance").value,
        pseudo: document.getElementById("pseudo").value,
        genre: document.getElementById("genre").value,
        poste: document.getElementById("poste").value
    };

    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            window.location.href = "http://localhost/public/testPages/testProfil.html";
        }
    })
    .catch(error => console.error('Erreur lors de la mise à jour:', error));
}

// Gestion du formulaire d'édition
document.addEventListener("DOMContentLoaded", function() {
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("id");
    if (userId) {
        fetch('http://localhost/public/api/userApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getUser', id_utilisateur: userId })
        })
        .then(response => response.json())
        .then(user => {
            document.getElementById("user_id").value = user.id_utilisateur;
            document.getElementById("nom").value = user.nom;
            document.getElementById("prenom").value = user.prenom;
            document.getElementById("email").value = user.email;
            document.getElementById("annee_naissance").value = user.annee_naissance;
            document.getElementById("pseudo").value = user.pseudo;
            document.getElementById("genre").value = user.genre;
            document.getElementById("poste").value = user.poste;
        });

        document.getElementById("editForm").addEventListener("submit", function(event) {
            event.preventDefault();
            updateUserProfile();
        });
    }
});

// Fonction pour supprimer un utilisateur
function deleteUser(userId) {
    const confirmation = confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?");
    if (confirmation) {
        const userData = {
            action: 'deleteUser',
            id_utilisateur: userId
        };

        fetch('http://localhost/public/api/userApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Affiche le message de l'API
            if (data.success) {
                // Si la suppression est réussie, redirige vers la page de profil
                window.location.href = "http://localhost/public/testPages/testProfil.html";
            }
        })
        .catch(error => console.error('Erreur lors de la suppression:', error));
    }
}

// Fonction pour rechercher des utilisateurs
function searchUsers() {
    const nom = document.getElementById("searchNom").value.trim();
    const prenom = document.getElementById("searchPrenom").value.trim();
    const poste = document.getElementById("searchPoste").value.trim();

    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'searchUsers', nom, prenom, poste })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.users);
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur lors de la recherche:', error));
}

// Fonction pour afficher les résultats et ajouter les boutons Modifier/Supprimer
function displaySearchResults(users) {
    const resultsTable = document.getElementById("searchResults");
    resultsTable.innerHTML = ""; // Vide le tableau avant d'afficher les nouveaux résultats

    users.forEach(user => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${user.nom}</td>
            <td>${user.prenom}</td>
            <td>${user.email}</td>
            <td>${user.annee_naissance}</td>
            <td>${user.pseudo}</td>
            <td>${user.genre}</td>
            <td>${user.poste}</td>
            <td>
                <button onclick="redirectToEdit(${user.id_utilisateur})">Modifier</button>
                <button onclick="deleteUser(${user.id_utilisateur})">Supprimer</button>
            </td>
        `;
        resultsTable.appendChild(row);
    });
}

// Fonction pour changer le mot de passe
function updatePassword() {
    const ancienMotDePasse = document.getElementById("ancien_mot_de_passe").value;
    const nouveauMotDePasse = document.getElementById("nouveau_mot_de_passe").value;

    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'updatePassword',
            ancien_mot_de_passe: ancienMotDePasse,
            nouveau_mot_de_passe: nouveauMotDePasse
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById("passwordForm").reset();
            setTimeout(() => {
                window.location.href = "http://localhost/public/testPages/testLogin.html";
            }, 1000);
        }
    })
    .catch(error => console.error('Erreur lors du changement de mot de passe:', error));
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



    






