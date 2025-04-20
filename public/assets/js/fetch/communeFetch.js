// Fonction pour rechercher les communes
function searchCommunes() {
    // Récupérer les valeurs des champs
    const codeCommune = document.getElementById("code_commune").value;
    const cpCommune = document.getElementById("cp_commune").value;
    const idCommune = document.getElementById("id_commune_search").value;
    

    // Si tous les champs sont vides, on envoie une requête pour toutes les communes
    const searchData = {
        action: 'searchCommune',
        ...(codeCommune ? { code_commune: codeCommune } : {}),
        ...(cpCommune ? { cp_commune: cpCommune } : {}),
        ...(idCommune ? { id_commune: idCommune } : {})
        
    };

    // Envoyer la requête
    fetch('http://localhost/public/api/communeApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data)
        displayCommunes(data);
    })
    .catch(error => console.error('Erreur lors de la recherche:', error));
}

// Fonction pour initialiser le formulaire de recherche
function initCommuneSearchForm() {
    const form = document.getElementById("communeSearchForm");
    if (form) {
        loadCommunes();
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            searchCommunes();
        });
    }
}
window.initCommuneSearchForm = initCommuneSearchForm;

// Fonction pour afficher les résultats dans un tableau
function displayCommunes(communes) {
    const tableContainer = document.getElementById("communeTableContainer");
    const tableBody = document.getElementById("communeResults");

    if (!tableBody || !tableContainer) {
        console.error("Élément(s) manquant(s) dans le DOM : #communeResults ou #communeTableContainer");
        return;
    }

    tableBody.innerHTML = "";

    if (!Array.isArray(communes) || communes.length === 0) {
        tableContainer.style.display = "none"; // Cache le tableau s'il n'y a rien à afficher

        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-danger">
                    Aucune commune trouvée.
                </td>
            </tr>
        `;
        return;
    }

    tableContainer.style.display = "block"; // Affiche le tableau

    communes.forEach(commune => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${commune.nom_commune || "N/A"}</td>
            <td>${commune.code_commune || "N/A"}</td>
            <td>${commune.cp_commune || "N/A"}</td>
            <td>${commune.email_commune || "N/A"}</td>
            <td>${commune.tel_commune || "N/A"}</td>
            <td>${commune.adresse_commune || "N/A"}</td>
            <td>${commune.contact || "N/A"}</td>
            <td>${commune.reseau_instruction || "N/A"}</td>
            <td>${commune.urbaniste_vra || "N/A"}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="redirectToEdit(${commune.id_commune})"><i class="bi bi-pencil-fill fs-5"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteCommune(${commune.id_commune})"><i class="bi bi-trash-fill fs-5"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Fonction pour charger la liste des communes dans le <select>
function loadCommunes() {
    fetch('http://localhost/public/api/communeApi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getCommunes' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.communes) {
            const selects = [
                document.getElementById("id_commune_search"),
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

// Redirection vers l'édition d'une commune
function redirectToEdit(communeId) {
    changePage(`/editCommune?id=${communeId}`);
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
            window.location.href = '/communes';
             
        } else {
            alert("Erreur lors de l'ajout de la commune : " + data.message);
        }
    })
    .catch(error => {
        console.error("Erreur lors de l'ajout de la commune :", error);
    });
}
//Fonction pour initialiser le formulaire d'ajout d'une commune
function initAddCommuneForm() {
    const form = document.getElementById("addCommuneForm");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            addCommune(); 
        });
    } else {
        console.warn("⚠️ Formulaire d'ajout de commune introuvable !");
    }
}
window.initAddCommuneForm = initAddCommuneForm;

// Fonction pour charger les détails d'une commune pour modification
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
            if (data) {
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
            } else {
                alert("Données de commune introuvables.");
            }
        })
        .catch(error => console.error('Erreur lors du chargement des données:', error));
    }
}

// Fonction pour initialiser le formulaire de modification de commune
function initLoadCommuneDetails() {
    loadCommuneDetails();  // Appel à la fonction de chargement des données
}
// Appeler initLoadCommuneDetails lors du chargement de la page
window.onload = initLoadCommuneDetails;

// Fonction pour Mettre à jour une commune
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
            window.location.href = '/communes';
        }
    })
    .catch(error => console.error('Erreur lors de la mise à jour:', error));
}

// Fonction pour initialiser le formulaire de modification de commune
function initUpdateCommuneForm() {
    const form = document.getElementById("editCommuneForm");
    if (form) {
        form.addEventListener("submit", updateCommune);
    }
}
// Appeler initUpdateCommuneForm lors du chargement de la page
window.onload = initUpdateCommuneForm;


// Fonction pour supprimer une commune avec callback
function deleteCommune(communeId, callback) {
    if (confirm("Voulez-vous vraiment supprimer cette commune ?")) {
        fetch('http://localhost/public/api/communeApi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'deleteCommune', id_commune: communeId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (typeof callback === 'function') {
                callback();
            } else {
                window.location.href = '/communes';
            }
        })
        .catch(error => console.error('Erreur lors de la suppression:', error));
    }
}



