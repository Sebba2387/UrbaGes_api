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

                if (prenomInput && nomInput && emailInput && anneeNaissanceInput && genreInput && pseudoInput && posteInput) {
                    prenomInput.value = data.user.prenom;
                    nomInput.value = data.user.nom;
                    emailInput.value = data.user.email;
                    anneeNaissanceInput.value = data.user.annee_naissance;
                    genreInput.value = data.user.genre;
                    pseudoInput.value = data.user.pseudo;
                    posteInput.value = data.user.poste;
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

