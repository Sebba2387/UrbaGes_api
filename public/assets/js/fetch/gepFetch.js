// Fonction pour initialiser le formulaire de recherche
function initGepSearchForm() {
    const form = document.getElementById("gepSearchForm");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            searchGep();
        });
    }
    loadCommunes(function() {
    });
}

// Fonction pour charger les noms des communes
function loadCommunes(callback) {
    const communeSelect = document.getElementById("nom_commune");
    if (!communeSelect) return;
    fetch('http://localhost/public/api/gepApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getNomCommunes' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.nom_communes) {
            communeSelect.innerHTML = "<option value=''>-- Sélectionner une commune --</option>";
            data.nom_communes.forEach(item => {
                const option = document.createElement("option");
                option.value = item.nom_commune;
                option.textContent = item.nom_commune;
                communeSelect.appendChild(option);
            });
        } else {
            console.error("Aucune commune trouvée ou réponse invalide.");
        }
        if (callback) {
            callback();
        }
    })
    .catch(error => {
        console.error("Erreur lors du chargement des communes:", error);
    });
}

// Fonction pour lancer la recherche des données d'entrer d'une GEP
function searchGep() {
    const searchData = {
        action: 'searchGep',
        nom_commune: document.getElementById("nom_commune").value,
        section: document.getElementById("section").value,
        numero: document.getElementById("numero").value
    };
    fetch('http://localhost/public/api/gepApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        displayGep(data);
    })
    .catch(error => {
        console.error('Erreur lors de la recherche:', error);
    });
}

// Fonction pour afficher les résultats dans un tableau
function displayGep(geps) {
    const tableContainer = document.getElementById("gepTableContainer");
    const tableBody = document.getElementById("gepResults");

    if (!tableContainer || !tableBody) {
        console.error("Élément(s) manquant(s) : #gepTableContainer ou #gepResults");
        return;
    }

    tableBody.innerHTML = "";

    if (!Array.isArray(geps) || geps.length === 0) {
        tableContainer.style.display = "none";

        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-danger">
                    Aucune donnée GEP trouvée.
                </td>
            </tr>
        `;
        return;
    }

    tableContainer.style.display = "block";

    geps.forEach(gep => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${gep.nom_commune || "N/A"}</td>
            <td>${gep.section || "N/A"}</td>
            <td>${gep.numero || "N/A"}</td>
            <td>${gep.surface || "N/A"}</td>
            <td>${gep.captage || "N/A"}</td>
            <td>${gep.captage_regles || "N/A"}</td>
            <td>${gep.dys_pct || "N/A"}</td>
            <td>${gep.sage_indice || "N/A"}</td>
            <td>${gep.montana_zone || "N/A"}</td>
            <td>${gep.plu_sous_zone || "N/A"}</td>
            <td>${gep.plu_regle || "N/A"}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Appel de la fonction d'initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", initGepSearchForm);
