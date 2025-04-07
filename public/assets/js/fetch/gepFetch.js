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
    .catch(error => console.error('Erreur lors de la recherche:', error));
}

// Fonction pour afficher les résultats dans un tableau
function displayGep(geps) {
    const tableBody = document.getElementById("gepResults");
    tableBody.innerHTML = "";

    geps.forEach(gep => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${gep.nom_commune}</td>
            <td>${gep.section}</td>
            <td>${gep.numero}</td>
            <td>${gep.surface}</td>
            <td>${gep.captage}</td>
            <td>${gep.captage_regles}</td>
            <td>${gep.dys_pct}</td>
            <td>${gep.sage_indice}</td>
            <td>${gep.montana_zone}</td>
            <td>${gep.plu_sous_zone}</td>
            <td>${gep.plu_regle}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Fonction pour charger la liste des communes dans le <select>
function loadCommunes() {
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
            communeSelect.innerHTML = `<option value="">-- Sélectionner une commune --</option>`;
            data.nom_communes.forEach(item => {
                const option = document.createElement("option");
                option.value = item.nom_commune;
                option.textContent = item.nom_commune;
                communeSelect.appendChild(option);
            });
        } else {
            console.error("Aucune commune trouvée.");
        }
    })
    .catch(error => console.error("Erreur lors du chargement des communes :", error));
}

// Charger les communes au chargement de la page
document.addEventListener("DOMContentLoaded", function() {
    loadCommunes();
});






