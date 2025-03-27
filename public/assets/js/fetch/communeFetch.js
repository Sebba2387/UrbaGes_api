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
            <td><button onclick="redirectToEdit(${commune.id_commune})">Modifier</button></td>
            <td><button onclick="deleteCommune(${commune.id_commune})">Supprimer</button></td>
        `;
        tableBody.appendChild(row);
    });
}

// Redirection vers l'édition d'une commune
function redirectToEdit(communeId) {
    window.location.href = `http://localhost/public/testPages/testEditCommune.html?id=${communeId}`;
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