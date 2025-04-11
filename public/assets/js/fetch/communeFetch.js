// Fonction pour rechercher les communes
function searchCommunes() {
    const searchData = {
        action: 'searchCommune',
        code_commune: document.getElementById("code_commune").value,
        nom_commune: document.getElementById("nom_commune").value,
        cp_commune: document.getElementById("cp_commune").value
    };
    fetch('http://localhost/public/api/communeApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        displayCommunes(data);
    })
    .catch(error => console.error('Erreur lors de la recherche:', error));
}
// Fonction pour initialiser le formulaire de recherche
function initCommuneSearchForm() {
    const form = document.getElementById("communeSearchForm");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            searchCommunes();
        });
    }
}
window.initCommuneSearchForm = initCommuneSearchForm;


// Fonction pour afficher les résultats dans un tableau
function displayCommunes(communes) {
    const tableBody = document.getElementById("communeResults");
    tableBody.innerHTML = "";

    communes.forEach(commune => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${commune.nom_commune}</td>
            <td>${commune.code_commune}</td>
            <td>${commune.cp_commune}</td>
            <td>${commune.email_commune}</td>
            <td>${commune.tel_commune}</td>
            <td>${commune.adresse_commune}</td>
            <td>${commune.contact}</td>
            <td>${commune.reseau_instruction}</td>
            <td>${commune.urbaniste_vra}</td>
            <td>
            <button onclick="redirectToEdit(${commune.id_commune})"><i class="bi bi-pencil-fill fs-5"></i></button>
            <button onclick="deleteCommune(${commune.id_commune})"><i class="bi bi-trash-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Redirection vers l'édition d'une commune
function redirectToEdit(communeId) {
    window.location.href = `http://localhost/public/testPages/testEditCommune.html?id=${communeId}`;
}

// Fonction pour ajouter une nouvelle commune via l'API
function addCommune() {
    // Récupérer les données du formulaire
    const codeCommune = document.getElementById("code_commune").value;
    const nomCommune = document.getElementById("nom_commune").value;
    const cpCommune = document.getElementById("cp_commune").value;
    const emailCommune = document.getElementById("email_commune").value;
    const telCommune = document.getElementById("tel_commune").value;
    const adresseCommune = document.getElementById("adresse_commune").value;
    const contact = document.getElementById("contact").value;
    const reseauInstruction = document.getElementById("reseau_instruction").value;
    const urbanisteVra = document.getElementById("urbaniste_vra").value;

    // Vérification des champs requis
    if (!codeCommune || !nomCommune || !cpCommune || !emailCommune || !telCommune || !adresseCommune || !contact || !reseauInstruction || !urbanisteVra) {
        alert("Tous les champs doivent être remplis.");
        return;
    }

    // Créer l'objet de données à envoyer
    const communeData = {
        action: "addCommune",
        code_commune: codeCommune,
        nom_commune: nomCommune,
        cp_commune: cpCommune,
        email_commune: emailCommune,
        tel_commune: telCommune,
        adresse_commune: adresseCommune,
        contact: contact,
        reseau_instruction: reseauInstruction,
        urbaniste_vra: urbanisteVra
    };

    // Envoyer la requête POST à l'API
    fetch('http://localhost/public/api/communeApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(communeData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Commune ajoutée avec succès !");
            window.location.href = 'http://localhost/public/testPages/testCommune.html';  // Rediriger vers la liste des communes
        } else {
            alert("Erreur lors de l'ajout de la commune : " + data.message);
        }
    })
    .catch(error => {
        console.error("Erreur lors de l'ajout de la commune :", error);
    });
}

// Charger les détails d'une commune pour modification
function loadCommuneDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const id_commune = urlParams.get('id');

    if (id_commune) {
        fetch('http://localhost/public/api/communeApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getCommune', id_commune })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("id_commune").value = data.id_commune;
            document.getElementById("nom_commune").value = data.nom_commune;
            document.getElementById("code_commune").value = data.code_commune;
            document.getElementById("cp_commune").value = data.cp_commune;
            document.getElementById("email_commune").value = data.email_commune;
            document.getElementById("tel_commune").value = data.tel_commune;
            document.getElementById("adresse_commune").value = data.adresse_commune;
            document.getElementById("contact").value = data.contact;
            document.getElementById("reseau_instruction").value = data.reseau_instruction;
            document.getElementById("urbaniste_vra").value = data.urbaniste_vra;
            
        })
        .catch(error => console.error('Erreur lors du chargement des données:', error));
    }
}

// Mettre à jour une commune
function updateCommune() {
    const updateData = {
        action: 'updateCommune',
        id_commune: document.getElementById("id_commune").value,
        code_commune: document.getElementById("code_commune").value,
        nom_commune: document.getElementById("nom_commune").value,
        cp_commune: document.getElementById("cp_commune").value,
        email_commune: document.getElementById("email_commune").value,
        tel_commune : document.getElementById("tel_commune").value,
        adresse_commune : document.getElementById("adresse_commune").value,
        contact : document.getElementById("contact").value,
        reseau_instruction : document.getElementById("reseau_instruction").value,
        urbaniste_vra : document.getElementById("urbaniste_vra").value,
    };

    fetch('http://localhost/public/api/communeApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updateData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            window.location.href = "http://localhost/public/testPages/testCommune.html";
        }
    })
    .catch(error => console.error('Erreur lors de la mise à jour:', error));
}

// Suppression d'une commune
function deleteCommune(communeId) {
    if (confirm("Voulez-vous vraiment supprimer cette commune ?")) {
        fetch('http://localhost/public/api/communeApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'deleteCommune', id_commune: communeId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            searchCommunes();
        })
        .catch(error => console.error('Erreur lors de la suppression:', error));
    }
}


