// Préparation des fonction
window.onload = function () {
    initDossierSearchForm(() => {
        console.log("Formulaire et recherche de dossier prêts !");
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
function searchDossier(callback) {
    const requestData = {
        action: "searchDossier",
        id_commune: document.getElementById("id_commune_search").value.trim(),
        numero_dossier: document.getElementById("numero_dossier_search").value.trim(),
        id_cadastre: document.getElementById("id_cadastre_search").value.trim(),
        type_dossier: document.getElementById("type_dossier_search").value.trim(),
        sous_type_dossier: document.getElementById("sous_type_dossier_search").value.trim(),
    };
    fetch("http://localhost/public/api/dossierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDossiers(data.dossiers, callback);
        } else {
            console.error("Erreur :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}

// Fonction pour initialiser le formulaire de recherche
function initDossierSearchForm(callback) {
    const form = document.getElementById("searchDossierForm");
    if (!form) {
        console.error("Formulaire de recherche non trouvé");
        return;
    }
    loadCommunes();
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        searchDossier(() => {
            if (callback) callback();
        });
    });
    if (callback) callback();
}

// Fonction pour afficher des résultats de recherche
function displayDossiers(dossiers, callback) {
    const tableContainer = document.getElementById("dossierTableContainer");
    const tableBody = document.getElementById("dossierTableBody");
    if (!tableBody || !tableContainer) {
        console.error("Élément(s) manquant(s) dans le DOM : #dossierTableBody ou #dossierTableContainer");
        return;
    }
    tableBody.innerHTML = "";
    if (dossiers.length === 0) {
        tableContainer.style.display = "none";
        tableBody.innerHTML = `<tr><td colspan="12">Aucun dossier trouvé.</td></tr>`;
        return;
    }
    tableContainer.style.display = "block";

    dossiers.forEach(dossier => {
        const row = document.createElement("tr");
        row.id = `dossier-${dossier.id_dossier}`;
        row.innerHTML = `
            <td>${dossier.id_dossier || "N/A"}</td>
            <td>${dossier.nom_commune || "N/A"}</td>
            <td>${dossier.numero_dossier || "N/A"}</td>
            <td>${dossier.id_cadastre || "N/A"}</td>
            <td>${dossier.type_dossier || "N/A"}</td>
            <td>${dossier.sous_type_dossier || "N/A"}</td>
            <td>${dossier.pseudo || "N/A"}</td>
            <td>${dossier.libelle || "N/A"}</td>
            <td>${dossier.date_demande || "N/A"}</td>
            <td>${dossier.date_limite || "N/A"}</td>
            <td>${dossier.statut || "N/A"}</td>
            <td>${dossier.lien_calypso ? `<a href="${dossier.lien_calypso}" target="_blank">Lien</a>` : 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${dossier.id_dossier})"><i class="bi bi-pencil-fill fs-5"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteDossier(${dossier.id_dossier})"><i class="bi bi-trash-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
    paginateDossiers(callback);
}


// Fonction pour la pagination du résultat de la recherche
let itemsPerPage = 10;  // Nombre de lignes à afficher par page
let currentPage = 1;   // Page active

function paginateDossiers(callback) {
    const tableBody = document.getElementById("dossierTableBody");
    const rows = Array.from(tableBody.getElementsByTagName("tr"));
    rows.forEach(row => row.style.display = "none");
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    rows.slice(start, end).forEach(row => row.style.display = "");
    updatePagination(rows.length);
    if (callback) callback(); // callback une fois que la pagination est faite
}

// Fonction pour mettre à jour la pagination
function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById("pagination");
    if (!paginationContainer) return;

    paginationContainer.innerHTML = ""; // Reset

    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement("button");
        pageButton.textContent = i;
        pageButton.classList.add("btn", "me-1");
        if (i === currentPage) {
            pageButton.classList.add("btn-primary");
        } else {
            pageButton.classList.add("btn-outline-primary");
        }
        pageButton.addEventListener("click", () => {
            currentPage = i;
            paginateDossiers();
        });
        paginationContainer.appendChild(pageButton);
    }
}


// Fonction pour charger la liste des communes dans le <select>
function loadCommunes() {
    fetch('http://localhost/public/api/dossierApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getCommunes' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.communes) {
            const selects = [
                document.getElementById("id_commune_search"),
                document.getElementById("id_commune_add")
            ];

            selects.forEach(select => {
                if (select) {
                    select.innerHTML = '<option value="" disabled selected>-- Choisir une commune --</option>';
                    data.communes.forEach(commune => {
                        const option = document.createElement("option");
                        option.value = commune.id_commune;
                        option.textContent = commune.nom_commune;
                        select.appendChild(option);
                    });
                }
            });
        } else {
            console.error("Aucune commune trouvée.");
        }
    })
    .catch(error => console.error("Erreur lors du chargement des communes :", error));
}


// Fonction pour ajouter un dossier
function addDossier() {
    const data = {
        action: "addDossier",
        numero_dossier: document.getElementById("numero_dossier").value.trim(),
        id_cadastre: document.getElementById("id_cadastre").value.trim(),
        libelle: document.getElementById("libelle").value.trim(),
        date_demande: document.getElementById("date_demande").value.trim(),
        date_limite: document.getElementById("date_limite").value.trim(),
        statut: document.getElementById("statut").value.trim(),
        lien_calypso: document.getElementById("lien_calypso").value.trim(),
        type_dossier: document.getElementById("type_dossier").value,
        sous_type_dossier: document.getElementById("sous_type_dossier").value,
        id_commune: document.getElementById("id_commune").value
    };
    fetch("http://localhost/public/api/dossierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Dossier ajouté avec succès !");
            changePage('/dossiers');
        } else {
            alert("Erreur lors de l'ajout du dossier.");
        }
    })
    .catch(err => {
        console.error("Erreur lors de l'ajout du dossier :", err);
        alert("Erreur lors de l'ajout du dossier.");
    });
}

// Initialisation du formulaire d’ajout de dossier avec callback
function initAddDossierForm(callback) {
    const form = document.getElementById("addDossierForm");
    if (!form) {
        console.error("Formulaire d'ajout non trouvé");
        return;
    }
    // Charger les communes dès l'init
    loadCommunes();
    // Ajouter l'écouteur de soumission
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        addDossier();
        if (callback) callback();
    });

    // Callback après init
    if (callback) callback();
}

// Fonction pour supprimer un dossier
function deleteDossier(id_dossier) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce dossier ?")) {
        const data = {
            action: 'deleteDossier',
            id_dossier: id_dossier
        };
        fetch("http://localhost/public/api/dossierApi.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                const row = document.getElementById(`dossier-${id_dossier}`);
                if (row) {
                    row.remove();
                }
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error("Erreur lors de la suppression du dossier :", err);
            alert("Erreur lors de la suppression du dossier.");
        });
    }
}

// Fonction pour rediriger vers la page de modification d'un PLU
function redirectToEdit(id_dossier) {
    changePage(`/editDossier?id_dossier=${id_dossier}`);
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
            changePage('/dossiers');
        } else {
            alert("Erreur lors de la mise à jour du dossier.");
        }
    })
    .catch(err => {
        console.error("Erreur lors de la mise à jour :", err);
        alert("Erreur lors de la mise à jour.");
    });
}

// Fonction pour initialisation du formulaire d'édition du dossier
function initEditDossierForm() {
    const id_dossier = new URLSearchParams(window.location.search).get("id_dossier");
    if (!id_dossier) {
        console.error("Aucun ID de dossier trouvé dans l'URL.");
        return;
    }
    getDossierById(id_dossier);
    const form = document.getElementById("editDossierForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            updateDossier();
        });
    } else {
        console.warn("Formulaire 'editDossierForm' introuvable.");
    }
}


// Fonction pour récupérer l'id_utilisateur et afficher les dossiers associés
function fetchUserDossiers() {
    // Vérifie si l'utilisateur est connecté
    const userId = localStorage.getItem('userId');
    if (!userId) {
        alert("Utilisateur non connecté !");
        return;
    }
    // Envoi de la requête pour récupérer les dossiers de l'utilisateur
    fetch('http://localhost/public/api/dossierApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'getDossiersByUser',
            userId: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Insertion des dossiers dans le tableau
            const tbody = document.getElementById('mesDossiersTBody');
            tbody.innerHTML = ''; // Vider le tableau avant d'ajouter les nouveaux dossiers
            if (data.dossiers.length > 0) {
                data.dossiers.forEach(dossier => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${dossier.id_dossier}</td>
                            <td>${dossier.numero_dossier}</td>
                            <td>${dossier.nom_commune}</td>
                            <td>${dossier.type_dossier}</td>
                            <td>${dossier.sous_type_dossier}</td>
                            <td>${dossier.libelle}</td>
                            <td>${dossier.date_demande}</td>
                            <td>${dossier.statut}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${dossier.id_dossier})"><i class="bi bi-pencil-fill fs-5"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="deleteDossier(${dossier.id_dossier})"><i class="bi bi-trash-fill fs-5"></i></button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6">Aucun dossier trouvé.</td></tr>';
            }
        } else {
            alert(data.message || 'Erreur lors de la récupération des dossiers.');
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error);
        alert('Impossible de charger les dossiers.');
    });
}

// Fonction pour initialiser le fetch dès que le DOM est chargé
function initFetchUserDossiers() {
    const tbody = document.getElementById('mesDossiersTBody');
    if (tbody) {
        const callback = tbody.getAttribute('data-callback');
        if (callback && typeof window[callback] === 'function') {
            console.log("Appel de la fonction : " + callback);
            window[callback](); // Appel de la fonction fetchUserDossiers
        } else {
            console.error('Aucun callback valide trouvé dans data-callback.');
        }
    }
}

// Initialisation lorsque le DOM est complètement chargé
document.addEventListener('DOMContentLoaded', initFetchUserDossiers);







  
  

  




