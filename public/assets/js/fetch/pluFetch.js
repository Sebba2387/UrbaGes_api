// Fonction pour rechercher les PLU avec callback
function searchPlu(callback) {
    const code_commune = document.getElementById("code_commune").value.trim();
    const nom_commune = document.getElementById("nom_commune").value.trim();
    const cp_commune = document.getElementById("cp_commune").value.trim();
    const etat_plu = document.getElementById("etat_plu").value.trim();
    // Vérifie si tous les champs sont vides
    if (!code_commune && !nom_commune && !cp_commune && !etat_plu) {
        const tableBody = document.getElementById("pluResults");
        tableBody.innerHTML = `
            <tr>
                <td colspan="14" class="text-center text-danger">
                    Veuillez entrer au moins un critère de recherche pour effectuer la recherche.
                </td>
            </tr>
        `;
        return; // Ne lance pas l'appel à l'API
    }
    const searchData = {
        action: 'searchPlu',
        code_commune,
        nom_commune,
        cp_commune,
        etat_plu
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
            return;
        }
        const pluList = data || [];
        displayPlu(pluList, callback);
    })
    .catch(error => console.error("Erreur lors de la récupération des PLUs :", error));
}

// Fonction pour afficher les résultats et exécuter un callback
function displayPlu(pluList, callback) {
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
                <button onclick="redirectToEdit(${plu.id_plu})"><i class="bi bi-pencil-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
    // Exécuter le callback s’il est fourni
    if (typeof callback === 'function') {
        callback();
    }
}

// Fonction pour initialiser le formulaire de recherche de PLU
function initSearchPluForm() {
    const form = document.getElementById("searchPluForm");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            searchPlu(); // Appel de la fonction searchPlu avec le callback optionnel
        });
    } else {
        console.warn("⚠️ Formulaire de recherche de PLU introuvable !");
    }
}
window.initSearchPluForm = initSearchPluForm;

// Fonction pour rediriger vers la page de modification d'un PLU
function redirectToEdit(id_plu) {
    window.location.href = `/editPlu?id=${id_plu}`;
}

// Fonction pour initialiser le formulaire d'édition de PLU
function initEditPluForm() {
    const urlParams = new URLSearchParams(window.location.search);
    const id_plu = urlParams.get('id');
    if (id_plu) {
        getPluById(id_plu, populateEditForm);
    } else {
        console.warn("ID du PLU manquant dans l'URL");
    }
}
window.initEditPluForm = initEditPluForm;

// Fonction pour récupérer les données du PLU en call-back
function getPluById(id_plu, callback) {
    const requestData = {
        action: 'getPluById',
        id_plu: id_plu
    };
    fetch('http://localhost/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.plu) {
            if (typeof callback === 'function') {
                callback(data.plu);
            }
        } else {
            console.error("Erreur lors de la récupération du PLU :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}

// Fonction pour pré-remplir le formulaire d'édition avec les données d'un PLU
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
// Appel de la fonction pour initialiser le formulaire d'édition de PLU
document.addEventListener("DOMContentLoaded", function () {
    initEditPluForm();
});

// Fonction pour mettre à jour les données d'un PLU concerné
function updatePlu() {
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
    fetch("http://localhost/public/api/pluApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("PLU mis à jour avec succès !");
            window.location.href = "/plu";
        } else {
            alert("Erreur : " + data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}
