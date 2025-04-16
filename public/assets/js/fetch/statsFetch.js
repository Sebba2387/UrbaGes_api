// Fonction principale pour récupérer les stats et exécuter un callback
function fetchStats(callback) {
    fetch('http://localhost/public/api/statsApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'getStats' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof callback === 'function') {
                callback(data.stats);
            }
        } else {
            console.error('Erreur lors de la récupération des statistiques');
        }
    })
    .catch(error => {
        console.error('Erreur de réseau ou serveur:', error);
    });
}

// Fonctions de rendu des graphiques
function createBarChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = data.map(item => item.year);
    const values = data.map(item => item.total);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        }
    });
}

function createDoughnutChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = data.map(item => item.pseudo || item.statut);
    const values = data.map(item => item.total);
    const total = values.reduce((sum, value) => sum + value, 0);
    const percentages = values.map(value => (value / total) * 100);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: percentages,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
            }]
        }
    });
}

// Initialise l'affichage des statistiques
function initStats() {
    fetchStats(stats => {
        // Chiffres clés
        document.getElementById('totalInstructions').textContent = stats.totalInstructions;
        document.getElementById('complexEnCours').textContent = stats.complexEnCours;
        document.getElementById('spgepEnCours').textContent = stats.spgepEnCours;
        document.getElementById('servitudeEnCours').textContent = stats.servitudeEnCours;
        document.getElementById('retrocessionEnCours').textContent = stats.retrocessionEnCours;

        // Graphiques
        createBarChart('instructionsGraph', stats.instructionsGraph);
        createDoughnutChart('instructionsParIntervenant', stats.instructionsParIntervenant);
        createBarChart('servitudeGraph', stats.servitudeGraph);
        createDoughnutChart('servitudeStatusGraph', stats.servitudeStatusGraph);
        createBarChart('retrocessionGraph', stats.retrocessionGraph);
        createDoughnutChart('retrocessionStatusGraph', stats.retrocessionStatusGraph);
    });
}

// Lancer au chargement complet de la page
window.addEventListener('load', initStats);
