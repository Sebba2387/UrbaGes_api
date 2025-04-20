// Préparation des fonctions
window.onload = function () {
    initCourrierSearchForm(() => {
        console.log("Formulaire et recherche de courriers prêts !");
    });
    document.getElementById("editCourrierForm")?.addEventListener("submit", function (e) {
        e.preventDefault();
        updateCourrier();
    });
    const urlParams = new URLSearchParams(window.location.search);
    const id_courrier = urlParams.get("id_courrier");
    if (id_courrier) {
        getCourrierById(id_courrier);
    }
};

// Fonction pour rechercher des courriers
function searchCourrier(callback) {
    const requestData = {
        action: "searchCourrier",
        id_courrier: document.getElementById("codeCourrierSearch").value.trim(),
        type_courrier: document.getElementById("typeCourrierSearch").value.trim(),
        libelle_courrier: document.getElementById("libelleCourrierSearch").value.trim(),
    };

    fetch("http://localhost/public/api/courrierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestData),
    })
    .then(response => response.json())
    .then(data => {
    if (Array.isArray(data) && data.length > 0) {
        displayCourriers(data, callback);
    } else {
        console.error("Aucun courrier trouvé ou erreur dans les données retournées.");
        displayCourriers([], callback);
    }
})
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}

// Fonction pour initialiser le formulaire de recherche
function initCourrierSearchForm(callback) {
    const form = document.getElementById("searchCourrierForm");
    if (!form) {
        console.error("Formulaire de recherche non trouvé");
        return;
    }
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        searchCourrier(() => {
            if (callback) callback();
        });
    });
    if (callback) callback();
}

// Fonction pour afficher les résultats
function displayCourriers(courriers, callback) {
    const tableContainer = document.getElementById("courrierTableContainer");
    const tableBody = document.getElementById("courrierTableBody");

    if (!tableBody || !tableContainer) {
        console.error("Élément(s) manquant(s) dans le DOM : #courrierTableBody ou #courrierTableContainer");
        return;
    }

    tableBody.innerHTML = ""; // Réinitialiser les résultats précédents

    if (courriers.length === 0) {
        tableContainer.style.display = "none";
        tableBody.innerHTML = `<tr><td colspan="6">Aucun courrier trouvé.</td></tr>`;
        return;
    }

    tableContainer.style.display = "block";
    courriers.forEach(courrier => {
        const row = document.createElement("tr");
        row.id = `courrier-${courrier.id_courrier}`;
        row.innerHTML = `
            <td>${courrier.id_courrier || "N/A"}</td>
            <td>${courrier.type_courrier || "N/A"}</td>
            <td>${courrier.libelle_courrier || "N/A"}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="redirectToEditCourrier(${courrier.id_courrier})"><i class="bi bi-pencil-fill fs-5"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteCourrier(${courrier.id_courrier})"><i class="bi bi-trash-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
    paginateCourriers(callback);
}

// Fonction pour la pagination du résultat de la recherche
let itemsPerPage = 10;  // Nombre de lignes à afficher par page
let currentPage = 1;   // Page active

function paginateCourriers(callback) {
    const tableBody = document.getElementById("courrierTableBody");
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

// Fonction pour ajouter un courrier
function addCourrier() {
    const data = {
        action: "addCourrier",
        type_courrier: document.getElementById("type_courrier").value.trim(),
        libelle_courrier: document.getElementById("libelle_courrier").value.trim(),
        corps_courrier: document.getElementById("corps_courrier").value.trim(),
    };
    fetch("http://localhost/public/api/courrierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Courrier ajouté avec succès !");
            changePage('/courriers');
        } else {
            alert("Erreur lors de l'ajout du courrier.");
        }
    })
    .catch(err => {
        console.error("Erreur lors de l'ajout du courrier :", err);
        alert("Erreur lors de l'ajout du courrier.");
    });
}

// Initialisation du formulaire d’ajout de dossier avec callback
function initAddCourrierForm(callback) {
    const form = document.getElementById("addCourrierForm");
    if (!form) {
        console.error("Formulaire d'ajout non trouvé");
        return;
    }
    // Ajouter l'écouteur de soumission
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        addCourrier();
        if (callback) callback();
    });
    // Callback après init
    if (callback) callback();
}

// Fonction pour supprimer un courrier
function deleteCourrier(id_courrier) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce courrier ?")) {
        const data = {
            action: 'deleteCourrier',
            id_courrier: id_courrier
        };
        fetch("http://localhost/public/api/courrierApi.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                const row = document.getElementById(`courrier-${id_courrier}`);
                if (row) {
                    row.remove();
                }
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error("Erreur lors de la suppression du courrier :", err);
            alert("Erreur lors de la suppression du courrier.");
        });
    }
}

// Fonction pour rediriger vers la page de modification d'un courrier
function redirectToEditCourrier(id_courrier) {
    changePage(`/editCourrier?id_courrier=${id_courrier}`);
}

// Fonction pour récupérer les infos d'un courrier et préremplir le formulaire d'édition
function getCourrierById(id_courrier) {
    fetch("http://localhost/public/api/courrierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "getCourrierById", id_courrier })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const courrier = data.courrier.courrier;

            // Appliquer les valeurs récupérées dans le formulaire
            const elements = {
                "id_courrier": courrier.id_courrier,
                "type_courrier": courrier.type_courrier,
                "libelle_courrier": courrier.libelle_courrier,
                "corps_courrier": courrier.corps_courrier,
            };
            Object.keys(elements).forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = elements[id];
                } else {
                    console.warn(`L'élément avec l'ID ${id} est manquant.`);
                }
            });
        } else {
            console.error("Erreur lors de la récupération du courrier :", data.message);
        }
    })
    .catch(error => console.error("Erreur de connexion à l'API :", error));
}

// Fonction pour mettre à jour les informations d'un courrier
function updateCourrier() {
    const id_courrier = new URLSearchParams(window.location.search).get("id_courrier");
    const data = {
        action: "updateCourrier",
        id_courrier,
        type_courrier: document.getElementById("type_courrier").value.trim(),
        libelle_courrier: document.getElementById("libelle_courrier").value.trim(),
        corps_courrier: document.getElementById("corps_courrier").value.trim(),
    };

    fetch("http://localhost/public/api/courrierApi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Courrier mis à jour avec succès !");
            changePage('/courriers');
        } else {
            alert("Erreur lors de la mise à jour du courrier.");
        }
    })
    .catch(err => {
        console.error("Erreur lors de la mise à jour :", err);
        alert("Erreur lors de la mise à jour.");
    });
}

// Fonction d'initialisation du formulaire d'édition du courrier
function initEditCourrierForm() {
    const id_courrier = new URLSearchParams(window.location.search).get("id_courrier");
    if (!id_courrier) {
        console.error("Aucun ID de courrier trouvé dans l'URL.");
        return;
    }
    getCourrierById(id_courrier);
    const form = document.getElementById("editCourrierForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            updateCourrier();
        });
    } else {
        console.warn("Formulaire 'editCourrierForm' introuvable.");
    }
}

// Fonction d'initialisation du formulaire pour générer un courrier
function initGenererCourrierForm() {
    const form = document.getElementById("genererCourrierForm");

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            genererCourrier(e); // Appel de la fonction genererCourrier avec l'événement
        });
    } else {
        console.warn("Formulaire 'genererCourrierForm' introuvable.");
    }
}

// Fonction pour générer le courrier
async function genererCourrier(event) {
    // Empêcher le comportement par défaut du formulaire
    event.preventDefault();
    const id_courrier = document.querySelector('#id_courrier').value.trim();
    const id_dossier = document.querySelector('#id_dossier').value.trim();

    // Vérification des champs
    if (!id_courrier || !id_dossier) {
        alert("Veuillez remplir les deux champs.");
        return;
    }
    try {
        const response = await fetch('http://localhost/public/api/courrierApi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'genererCourrier',
                id_courrier: id_courrier,
                id_dossier: id_dossier
            })
        });
        const textResponse = await response.text();
        const result = JSON.parse(textResponse);
        if (result.success) {
            const { courrier, dossier } = result.data;
            const corpsTemplate = courrier.corps_courrier;
            const corpsFinal = corpsTemplate.replace(/{{(.*?)}}/g, (match, key) => {
                const k = key.trim();
                const value = dossier[k];
                return value ?? `[${k} non trouvé]`;
            });
            const textarea = document.querySelector('#corps_courrier_gen');
            if (textarea) {
                textarea.value = corpsFinal;
            } else {
                console.warn("Textarea #corps_courrier introuvable dans le DOM !");
            }
        }
    } catch (error) {
        console.error("Erreur de récupération des données :", error);
        alert("Une erreur est survenue lors de la génération du courrier.");
    }
}

// Attacher le gestionnaire d'événement au formulaire
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', genererCourrier);
} else {
    console.error("Formulaire non trouvé !");
}





  
  
  
  
  