// Fonction pour connecter l'utilisateur
function loginUser(email, password) {
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            // 'Authorization': 'Bearer your_token_here' 
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
            changePage('/profil');
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// Fonction pour récupérer le profil utilisateur
function fetchUserProfile() {
    const userId = localStorage.getItem('userId');
    if (!userId) {
        changePage('/');
        return;
    }
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            // 'Authorization': 'Bearer your_token_here'
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

// Fonction pour afficher les utilisateurs dans le tableau HTML
function displayUsersTable(users) {
    const tableBody = document.getElementById("usersTableBody");

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
                <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${user.id_utilisateur})"><i class="bi bi-pencil-fill fs-5"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id_utilisateur})"><i class="bi bi-trash-fill fs-5"></i></button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

// Fonction pour récupérer et afficher les utilisateurs
function fetchAllUsers() {
    const tableBody = document.getElementById("usersTableBody");
    if (!tableBody) {
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.userRole === 'admin' || data.userRole === 'moderateur') {
                displayUsersTable(data.users);
                tableBody.style.display = 'table-row-group';
            } else {
                tableBody.style.display = 'none';
                const message = document.createElement('p');
                message.textContent = 'Vous n\'avez pas les permissions nécessaires pour voir cette page.';
                document.body.appendChild(message);
            }
        } else {
            alert(data.message);
            tableBody.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Erreur lors de la récupération des utilisateurs :', error);
    });
}

// Fonction pour ajouter un nouveau utilisateur
function initRegisterForm() {
    const registerForm = document.getElementById("registerForm");

    if (registerForm) {
        registerForm.addEventListener("submit", (event) => {
            event.preventDefault();
            const formData = {
                action: 'registerUser',
                nom: document.getElementById("nom").value,
                prenom: document.getElementById("prenom").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
                annee_naissance: document.getElementById("annee_naissance").value,
                pseudo: document.getElementById("pseudo").value,
                genre: document.getElementById("genre").value,
                poste: document.getElementById("poste").value
            };
            fetch('http://localhost/public/api/userApi.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    changePage('/monEquipe');
                }
            })
            .catch(error => console.error('❌ Erreur lors de la requête :', error));
        });
    } else {
        console.warn("⚠️ Formulaire introuvable !");
    }
}
document.addEventListener("DOMContentLoaded", initRegisterForm);

// Fonction pour initialiser le formulaire de recherche
function searchUsers() {
    const searchForm = document.getElementById("searchForm");
    if (searchForm) {
        searchForm.addEventListener("submit", (event) => {
            event.preventDefault();
            const searchNomElement = document.getElementById("searchNom");
            const searchPrenomElement = document.getElementById("searchPrenom");
            const searchPosteElement = document.getElementById("searchPoste");
            if (!searchNomElement || !searchPrenomElement || !searchPosteElement) {
                console.error('❌ Un ou plusieurs champs de recherche sont introuvables !');
                return;
            }
            const nom = searchNomElement.value.trim();
            const prenom = searchPrenomElement.value.trim();
            const poste = searchPosteElement.value.trim();

            const formData = {
                action: 'searchUsers',
                nom: nom,
                prenom: prenom,
                poste: poste
            };
            fetch('http://localhost/public/api/userApi.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.users); // Fonction pour afficher les utilisateurs
                } else {
                    alert(data.message || 'Aucun utilisateur trouvé');
                }
            })
            .catch(error => console.error('❌ Erreur lors de la requête :', error));
        });
    } else {
        console.warn("⚠️ Formulaire de recherche introuvable !");
    }
}

// Fonction pour afficher le résultat de la recherche
function displaySearchResults(users) {
    const tableContainer = document.getElementById("searchResultsTableContainer");
    const tableBody = document.getElementById("searchResultsTableBody");
    if (!tableBody) {
        console.error('L\'élément avec l\'ID "searchResultsTableBody" est introuvable.');
        return;
    }
    if (users.length === 0) {
        tableBody.innerHTML = "<tr><td colspan='10'>Aucun utilisateur trouvé.</td></tr>";
        tableContainer.style.display = "none";
    } else {

        tableContainer.style.display = "block";
        tableBody.innerHTML = "";

        users.forEach(user => {
            let row = `<tr>
                <td>${user.id_utilisateur || 'N/A'}</td>
                <td>${user.nom || 'N/A'}</td>
                <td>${user.prenom || 'N/A'}</td>
                <td>${user.email || 'N/A'}</td>
                <td>${user.annee_naissance || 'N/A'}</td>
                <td>${user.pseudo || 'N/A'}</td>
                <td>${user.genre || 'N/A'}</td>
                <td>${user.poste || 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${user.id_utilisateur})"><i class="bi bi-pencil-fill fs-5"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id_utilisateur})"><i class="bi bi-trash-fill fs-5"></i></button>
                </td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    }
}
document.addEventListener("DOMContentLoaded", function() {
    searchUsers();
});

// Fonction pour rediriger vers l'édition du profil
function redirectToEdit(userId) {
    changePage(`/editProfil?id=${userId}`);
}

// Fonction pour mettre à jour du profil utilisateur
function updateUserProfile(userId) {
    const userData = {
        action: 'updateUser',
        id_utilisateur: userId,
        nom: document.getElementById("nom").value.trim(),
        prenom: document.getElementById("prenom").value.trim(),
        email: document.getElementById("email").value.trim(),
        annee_naissance: document.getElementById("annee_naissance").value.trim(),
        pseudo: document.getElementById("pseudo").value.trim(),
        genre: document.getElementById("genre").value.trim(),
        poste: document.getElementById("poste").value.trim()
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
            changePage('/profil');
        }
    })
    .catch(error => console.error('❌ Erreur lors de la mise à jour :', error));
}

//Fonction pour initialiser le formulaire d'édition
function initEditForm() {
    const form = document.getElementById("editForm");
    if (!form) {
        console.warn("⚠️ Formulaire de profil non trouvé.");
        return;
    }
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("id");
    if (!userId) {
        console.warn("⚠️ Aucun ID utilisateur trouvé dans l'URL.");
        return;
    }
    // Pré-remplissage des champs avec les données utilisateur
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getUser', id_utilisateur: userId })
    })
    .then(response => response.json())
    .then(user => {
        if (!user || !user.id_utilisateur) {
            console.warn("⚠️ Utilisateur non trouvé.");
            return;
        }
        document.getElementById("user_id").value = user.id_utilisateur;
        document.getElementById("nom").value = user.nom;
        document.getElementById("prenom").value = user.prenom;
        document.getElementById("email").value = user.email;
        document.getElementById("annee_naissance").value = user.annee_naissance;
        document.getElementById("pseudo").value = user.pseudo;
        document.getElementById("genre").value = user.genre;
        document.getElementById("poste").value = user.poste;
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            updateUserProfile(user.id_utilisateur);
        });
    })
    .catch(error => console.error('❌ Erreur lors de la récupération du profil :', error));
}
// Appel au chargement du DOM
document.addEventListener("DOMContentLoaded", initEditForm);


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
                changePage('/profil');
            }
        })
        .catch(error => console.error('Erreur lors de la suppression:', error));
    }
}

// Fonction pour changer le mot de passe
function initPasswordForm() {
    const passwordForm = document.getElementById("passwordForm");
    if (!passwordForm) {
        console.warn("⚠️ Formulaire de changement de mot de passe introuvable !");
        return;
    }
    passwordForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const ancienInput = document.getElementById("ancien_mot_de_passe");
        const nouveauInput = document.getElementById("nouveau_mot_de_passe");
        if (!ancienInput || !nouveauInput) {
            console.error("❌ Champs de mot de passe non trouvés dans le DOM !");
            return;
        }
        const ancienMotDePasse = ancienInput.value.trim();
        const nouveauMotDePasse = nouveauInput.value.trim();
        if (!ancienMotDePasse || !nouveauMotDePasse) {
            alert("Veuillez remplir les deux champs de mot de passe.");
            return;
        }
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
                passwordForm.reset();
                changePage('/');
            }
        })
        .catch(error => console.error('❌ Erreur lors du changement de mot de passe :', error));
    });
}
// Activer au chargement de la page
document.addEventListener("DOMContentLoaded", initPasswordForm);


// Fonction pour déconnecter l'utilisateur
function logoutUser() {
    fetch('http://localhost/public/api/userApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('userId');
            changePage('/');
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur de déconnexion :', error));
}





