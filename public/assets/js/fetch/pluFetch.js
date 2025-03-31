// Fonction pour rechercher les PLU
function searchPlu() {
    const searchData = {
        action: 'searchPlu',  // Assurez-vous que cette clé et valeur sont bien présentes
        code_commune: document.getElementById("code_commune").value,
        nom_commune: document.getElementById("nom_commune").value,
        cp_commune: document.getElementById("cp_commune").value,
        etat_plu: document.getElementById("etat_plu").value
    };

    fetch('http://localhost/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === false) {
            console.error("Erreur de l'API :", data.message);
            return; // Stoppe l'exécution si l'API échoue
        }
        let pluList = data || []; // Si 'pluList' existe, l'utiliser
        displayPlu(pluList); // Affiche les PLUs si tout va bien
    })
    .catch(error => console.error("Erreur lors de la récupération des PLUs :", error));
}


// Fonction pour afficher les résultats dans un tableau
function displayPlu(pluList) {
    const tableBody = document.getElementById("pluResults");
    tableBody.innerHTML = "";

    pluList.forEach(plu => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${plu.nom_commune}</td>
            <td>${plu.code_commune}</td>
            <td>${plu.cp_commune}</td>
            <td>${plu.etat_plu}</td>
            <td>${plu.type_plu}</td>
            <td>${plu.date_plu}</td>
            <td>${plu.systeme_ass}</td>
            <td>${plu.statut_zonage}</td>
            <td>${plu.statut_pres}</td>
            <td>${plu.date_annexion}</td>
            <td>${plu.lien_zonage ? `<a href="${plu.lien_zonage}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>${plu.lien_dhua ? `<a href="${plu.lien_dhua}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>${plu.observation_plu}</td>
            <td>
                <button onclick="redirectToEdit(${plu.id_plu})">Modifier</button>
                <button onclick="deletePlu(${plu.id_plu})">Supprimer</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Fonction pour rediriger vers la page de modification d'un PLU
function redirectToEdit(id_plu) {
    window.location.href = `http://localhost/public/testPages/testEditPlu.html?id=${id_plu}`;
}

// Vérifier si on est bien sur testEditPlu.html avant d'appeler getPluById()
document.addEventListener("DOMContentLoaded", function () {
    if (window.location.pathname.includes("testEditPlu.html")) {
        const urlParams = new URLSearchParams(window.location.search);
        const id_plu = urlParams.get('id');

        if (id_plu) {
            getPluById(id_plu);
        } else {
            console.warn("ID du PLU manquant dans l'URL");
        }
    }
});

//Fonction pour récupérer ID du PLU concerné
function getPluById(id_plu) {
    const requestData = {
        action: 'getPluById',
        id_plu: id_plu
    };
    fetch('http://localhost/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            populateEditForm(data.plu); // Fonction pour remplir le formulaire de modification
        } else {
            console.error("Erreur lors de la récupération du PLU:", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API:", error));
}

// Fonction pour remplir le formulaire de modification avec les données du PLU
function populateEditForm(plu) {
    document.getElementById("id_plu").value = plu.id_plu;
    document.getElementById("etat_plu").value = plu.etat_plu;
    document.getElementById("type_plu").value = plu.type_plu;
    document.getElementById("date_plu").value = plu.date_plu;
    document.getElementById("systeme_ass").value = plu.systeme_ass;
    document.getElementById("statut_zonage").value = plu.statut_zonage;
    document.getElementById("statut_pres").value = plu.statut_pres;
    document.getElementById("date_annexion").value = plu.date_annexion;
    document.getElementById("lien_zonage").value = plu.lien_zonage;
    document.getElementById("lien_dhua").value = plu.lien_dhua;
    document.getElementById("observation_plu").value = plu.observation_plu;
}

// Fonction pour mettre à jour les données d'un PLU concerné
function updatePlu() {
    // Vérifie que l'objet data est bien défini
    let data = {
        action: "updatePlu",
        id_plu: document.getElementById("id_plu").value,
        type_plu: document.getElementById("type_plu").value,
        etat_plu: document.getElementById("etat_plu").value,
        date_plu: document.getElementById("date_plu").value,
        systeme_ass: document.getElementById("systeme_ass").value,
        statut_zonage: document.getElementById("statut_zonage").value,
        statut_pres: document.getElementById("statut_pres").value,
        date_annexion: document.getElementById("date_annexion").value,
        lien_zonage: document.getElementById("lien_zonage").value,
        lien_dhua: document.getElementById("lien_dhua").value,
        observation_plu: document.getElementById("observation_plu").value
    };

    fetch("../../app/controllers/pluController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("PLU mis à jour avec succès !");
            window.location.href = "testPlu.html";
        } else {
            alert("Erreur : " + data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}
