document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("searchDossierForm").addEventListener("submit", function (event) {
        event.preventDefault();
        searchDossier();
    });
});

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
        const row = `<tr>
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
            <td>${dossier.lien_calypso }</td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}
