window.onload = function () {
    document.getElementById("searchDossierForm")?.addEventListener("submit", function (event) {
        event.preventDefault();
        searchDossier();
    });

    document.getElementById("editDossierForm")?.addEventListener("submit", function (e) {
        e.preventDefault();
        updateDossier();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const id_dossier = urlParams.get("id_dossier");
    if (id_dossier) {
        getDossierById(id_dossier);
    }
};
// Fonction pour rechercher des dossiers
function searchDossier() {
    const requestData = {
        action: "searchDossier",
        nom_commune: document.getElementById("nom_commune").value.trim(),
        numero_dossier: document.getElementById("numero_dossier").value.trim(),
        id_cadastre: document.getElementById("id_cadastre").value.trim(),
        type_dossier: document.getElementById("type_dossier").value.trim(),
        sous_type_dossier: document.getElementById("sous_type_dossier").value.trim(),
    };

    fetch("http://localhost/public/api/dossierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDossiers(data.dossiers);
        } else {
            console.error("Erreur :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}
//Fonction pour afficher des résultats de recherche
function displayDossiers(dossiers) {
    const tableBody = document.getElementById("dossierTableBody");
    tableBody.innerHTML = "";

    dossiers.forEach(dossier => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${dossier.nom_commune}</td>
            <td>${dossier.numero_dossier}</td>
            <td>${dossier.id_cadastre}</td>
            <td>${dossier.type_dossier}</td>
            <td>${dossier.sous_type_dossier}</td>
            <td>${dossier.pseudo}</td>
            <td>${dossier.libelle}</td>
            <td>${dossier.date_demande}</td>
            <td>${dossier.date_limite}</td>
            <td>${dossier.statut}</td>
            <td>${dossier.lien_calypso ? `<a href="${dossier.lien_calypso}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>
                <button onclick="redirectToEdit(${dossier.id_dossier})">Modifier</button>
                <button onclick="deletePlu(${dossier.id_dossier})">Supprimer</button>
            </td>
            
        `;
        tableBody.appendChild(row);
    });
}
// Fonction pour rediriger vers la page de modification d'un PLU
function redirectToEdit(id_dossier) {
    window.location.href = `http://localhost/public/testPages/testEditDossier.html?id_dossier=${id_dossier}`;
}

// Fonction pour récupérer les infos d'un dossier et préremplir testEditDossier.html
function getDossierById(id_dossier) {
    fetch("http://localhost/public/api/dossierApi.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ action: "getDossierById", id_dossier })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const dossier = data.dossier;
            const utilisateurs = data.utilisateurs; // liste de tous les pseudos

            // Remplir le <select id="pseudo"> avec les pseudos disponibles
            const pseudoSelect = document.getElementById("pseudo");
            pseudoSelect.innerHTML = "";

            utilisateurs.forEach(user => {
                const option = document.createElement("option");
                option.value = user.pseudo;
                option.textContent = user.pseudo;
                pseudoSelect.appendChild(option);
            });

            // Appliquer les valeurs récupérées dans le formulaire
            const elements = {
                "nom_commune": dossier.nom_commune,
                "numero_dossier": dossier.numero_dossier,
                "id_cadastre": dossier.id_cadastre,
                "libelle": dossier.libelle,
                "pseudo": dossier.pseudo,
                "date_demande": dossier.date_demande,
                "date_limite": dossier.date_limite,
                "statut": dossier.statut,
                "lien_calypso": dossier.lien_calypso
            };

            Object.keys(elements).forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = elements[id];
                } else {
                    console.warn(`L'élément avec l'ID ${id} est manquant.`);
                }
            });

            // Select pour les types
            const selectValues = {
                "type_dossier": dossier.type_dossier,
                "sous_type_dossier": dossier.sous_type_dossier
            };

            Object.keys(selectValues).forEach(function(id) {
                const select = document.getElementById(id);
                if (select) {
                    select.value = selectValues[id];
                }
            });

        } else {
            console.error("Erreur lors de la récupération du dossier :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}

// Fonction pour mettre à jour les informations d'un dossier choisi
function updateDossier() {
    const id_dossier = new URLSearchParams(window.location.search).get("id_dossier");

    const data = {
        action: "updateDossier",
        id_dossier,
        numero_dossier: document.getElementById("numero_dossier").value.trim(),
        id_cadastre: document.getElementById("id_cadastre").value.trim(),
        pseudo: document.getElementById("pseudo").value, // Récupéré depuis le <select>
        libelle: document.getElementById("libelle").value.trim(),
        date_demande: document.getElementById("date_demande").value.trim(),
        date_limite: document.getElementById("date_limite").value.trim(),
        statut: document.getElementById("statut").value.trim(),
        lien_calypso: document.getElementById("lien_calypso").value.trim(),
        type_dossier: document.getElementById("type_dossier").value,
        sous_type_dossier: document.getElementById("sous_type_dossier").value
    };

    fetch("http://localhost/public/api/dossierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Dossier mis à jour avec succès !");
            window.location.href = "/public/testPages/testDossier.html";
        } else {
            alert("Erreur lors de la mise à jour du dossier.");
        }
    })
    .catch(err => {
        console.error("Erreur lors de la mise à jour :", err);
        alert("Erreur lors de la mise à jour.");
    });
}