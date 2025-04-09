export async function loadComponent(componentPath) {
    try {
        const response = await fetch(`/public/pages/${componentPath}`);
        const html = await response.text();

        const container = document.getElementById('main-page');
        if (!container) {
            throw new Error("L'élément 'main-page' est introuvable dans le DOM.");
        }

        container.innerHTML = html;

        // Recharger tous les <script> de la page injectée
        const scripts = container.querySelectorAll('script');
        const loadScriptPromises = [];

        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.type = oldScript.type || 'text/javascript';

            if (oldScript.src) {
                newScript.src = oldScript.src;
                newScript.defer = false; // Pas de defer, on attend avec load
                loadScriptPromises.push(new Promise((resolve, reject) => {
                    newScript.onload = resolve;
                    newScript.onerror = reject;
                }));
            } else {
                newScript.textContent = oldScript.textContent;
            }

            document.body.appendChild(newScript);
        });

        // Attendre que tous les scripts soient chargés avant de continuer
        await Promise.all(loadScriptPromises);

        // Vérifier que la fonction 'fetchAllUsers' est bien définie avant de l'appeler
        if (typeof window.fetchAllUsers === 'function') {
            window.fetchAllUsers();  // Appeler la fonction de récupération des utilisateurs si elle est définie
        } 

        // Une fois tous les scripts chargés, exécuter la fonction de callback si définie
        const callbackContainer = container.querySelector('[data-callback]');
        if (callbackContainer) {
            const callbackName = callbackContainer.getAttribute('data-callback');
            if (callbackName && typeof window[callbackName] === 'function') {
                window[callbackName]();
            } else {
                console.warn(`Fonction callback "${callbackName}" introuvable`);
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement du composant :', error);
    }
}
