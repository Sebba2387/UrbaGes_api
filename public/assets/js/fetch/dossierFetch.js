window.onload = function () {
    document.getElementById("searchDossierForm")?.addEventListener("submit", function (event) {
        event.preventDefault();
        searchDossier();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const id_dossier = urlParams.get("id_dossier");
    if (id_dossier) {
        getDossierById(id_dossier);
    }
};

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
        body: JSON.stringify({ action: "getDossierById", id_dossier }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const dossier = data.dossier;
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

            // Affecte les valeurs pour les champs de texte
            Object.keys(elements).forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = elements[id];
                } else {
                    console.warn(`L'élément avec l'ID ${id} est manquant.`);
                }
            });

            // Affecte les valeurs pour les <select>
            const selectValues = {
                "type_dossier": dossier.type_dossier,
                "sous_type_dossier": dossier.sous_type_dossier
            };

            Object.keys(selectValues).forEach(function(id) {
                const select = document.getElementById(id);
                if (select) {
                    select.value = selectValues[id];  // Définit la valeur sélectionnée du <select>
                } else {
                    console.warn(`Le <select> avec l'ID ${id} est manquant.`);
                }
            });
        } else {
            console.error("Erreur lors de la récupération du dossier :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}


