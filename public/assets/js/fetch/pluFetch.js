// Fonction pour rechercher les PLU avec callback
function searchPlu(callback) {
    const id_commune = document.getElementById("id_commune_search").value.trim();
    const statut_zonage = document.getElementById("statut_zonage").value.trim();
    const statut_pres = document.getElementById("statut_pres").value.trim();
    const etat_plu = document.getElementById("etat_plu").value.trim();

    if (!id_commune && !statut_zonage && !statut_pres && !etat_plu) {
        const tableBody = document.getElementById("pluResults");
        tableBody.innerHTML = `
            <tr>
                <td colspan="14" class="text-center text-danger">
                    Veuillez entrer au moins un critère de recherche.
                </td>
            </tr>
        `;
        return;
    }

    const searchData = {
        action: 'searchPlu',
        id_commune,
        statut_zonage,
        statut_pres,
        etat_plu
    };

    fetch('http://localhost:8080/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        const pluList = Array.isArray(data) ? data : data.plu || [];
        displayPlu(pluList, callback);
    })
    .catch(error => console.error("Erreur lors de la récupération des PLUs :", error));
}


// Fonction pour afficher les résultats et exécuter un callback
function displayPlu(pluList, callback) {
    const tableContainer = document.getElementById("pluTableContainer");
    const tableBody = document.getElementById("pluResults");

    if (!tableContainer || !tableBody) {
        console.error("Élément(s) introuvable(s) : #pluTableContainer ou #pluResults");
        return;
    }

    tableBody.innerHTML = "";

    if (!Array.isArray(pluList) || pluList.length === 0) {
        tableContainer.style.display = "none";
        tableBody.innerHTML = `
            <tr>
                <td colspan="14" class="text-center text-danger">
                    Aucun résultat trouvé pour la recherche PLU.
                </td>
            </tr>
        `;
        return;
    }

    tableContainer.style.display = "block";

    pluList.forEach(plu => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${plu.nom_commune || "N/A"}</td>
            <td>${plu.code_commune || "N/A"}</td>
            <td>${plu.cp_commune || "N/A"}</td>
            <td>${plu.etat_plu || "N/A"}</td>
            <td>${plu.type_plu || "N/A"}</td>
            <td>${plu.date_plu || "N/A"}</td>
            <td>${plu.systeme_ass || "N/A"}</td>
            <td>${plu.statut_zonage || "N/A"}</td>
            <td>${plu.statut_pres || "N/A"}</td>
            <td>${plu.date_annexion || "N/A"}</td>
            <td>${plu.lien_zonage ? `<a href="${plu.lien_zonage}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>${plu.lien_dhua ? `<a href="${plu.lien_dhua}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>${plu.observation_plu || "N/A"}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${plu.id_plu})"><i class="bi bi-pencil-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });

    if (typeof callback === 'function') {
        callback();
    }
}


// Initialisation du formulaire de recherche PLU
function initSearchPluForm() {
    const form = document.getElementById("searchPluForm");
    if (form) {
        loadCommunes();
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            searchPlu();
        });
    } else {
        console.warn("Formulaire de recherche de PLU introuvable !");
    }
}
window.initSearchPluForm = initSearchPluForm;

// Redirection vers la page de modification d'un PLU
function redirectToEdit(id_plu) {
    changePage(`/editPlu?id=${id_plu}`);
}

// Chargement des communes dans le <select>
function loadCommunes() {
    fetch('http://localhost:8080/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getCommunes' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.communes) {
            const select = document.getElementById("id_commune_search");
            if (select) {
                select.innerHTML = '<option value="" disabled selected>-- Choisir une commune --</option>';
                data.communes.forEach(commune => {
                    const option = document.createElement("option");
                    option.value = commune.id_commune;
                    option.textContent = commune.nom_commune;
                    select.appendChild(option);
                });
            }
        } else {
            console.error("Aucune commune trouvée.");
        }
    })
    .catch(error => console.error("Erreur lors du chargement des communes :", error));
}

// Initialisation du formulaire d'édition PLU
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

// Récupérer un PLU par son ID
function getPluById(id_plu, callback) {
    fetch('http://localhost:8080/public/api/pluApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getPluById', id_plu })
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
    .catch(error => console.error("Erreur API :", error));
}

// Pré-remplissage du formulaire d’édition
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

// Fonction pour mettre à jour d’un PLU
function updatePlu() {
    const data = {
        action: "updatePlu",
        id_plu: document.getElementById("id_plu").value,
        etat_plu: document.getElementById("etat_plu").value,
        type_plu: document.getElementById("type_plu").value,
        date_plu: document.getElementById("date_plu").value,
        systeme_ass: document.getElementById("systeme_ass").value,
        statut_zonage: document.getElementById("statut_zonage").value,
        statut_pres: document.getElementById("statut_pres").value,
        date_annexion: document.getElementById("date_annexion").value,
        lien_zonage: document.getElementById("lien_zonage").value,
        lien_dhua: document.getElementById("lien_dhua").value,
        observation_plu: document.getElementById("observation_plu").value
    };

    fetch("http://localhost:8080/public/api/pluApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert("PLU mis à jour avec succès !");
            changePage("/plu");
        } else {
            alert("Erreur lors de la mise à jour : " + result.message);
        }
    })
    .catch(error => console.error("Erreur de mise à jour :", error));
}
