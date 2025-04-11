export async function loadComponent(componentPath) {
    try {
        const response = await fetch(`/public/pages/${componentPath}`);
        const html = await response.text();

        const container = document.getElementById('main-page');
        if (!container) throw new Error("⚠️ L'élément #main-page est introuvable.");

        container.innerHTML = html;

        const scripts = container.querySelectorAll('script');
        const scriptPromises = [];

        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.type = oldScript.type || 'text/javascript';

            if (oldScript.src) {
                newScript.src = oldScript.src;
                newScript.defer = false;
                scriptPromises.push(new Promise((resolve, reject) => {
                    newScript.onload = resolve;
                    newScript.onerror = reject;
                }));
            } else {
                newScript.textContent = oldScript.textContent;
            }

            document.body.appendChild(newScript);
        });

        // ⏳ Attendre le chargement de tous les scripts
        await Promise.all(scriptPromises);

        // 🔁 Chercher TOUS les éléments ayant un data-callback
        const callbackElements = container.querySelectorAll('[data-callback]');
        callbackElements.forEach(el => {
            const callbackName = el.getAttribute('data-callback');
            if (callbackName && typeof window[callbackName] === 'function') {
                console.log(`🚀 Appel de la fonction : ${callbackName}()`);
                window[callbackName]();
            } else {
                console.warn(`⚠️ Fonction callback "${callbackName}" introuvable`);
            }
        });

    } catch (err) {
        console.error("❌ Erreur dans loadComponent :", err);
    }
}
