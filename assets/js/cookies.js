// cookies.js

// Fonction pour définir un cookie
function setCookie(name, value, days = 1) {
    const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}

// Fonction pour récupérer un cookie
function getCookie(name) {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [cookieName, cookieValue] = cookie.split('=').map(c => c.trim());
        if (cookieName === name) {
            return decodeURIComponent(cookieValue);
        }
    }
    return null;
}

// Fonction pour gérer les préférences des cookies
function handleCookiePreferences() {
    const popup = document.getElementById('cookie-consent-popup');
    const preferences = document.getElementById('cookie-preferences');
    const customizeBtn = document.getElementById('customize-btn');
    const acceptAllBtn = document.getElementById('accept-all-btn');
    const form = document.getElementById('cookie-form');

    // Vérifie si des préférences de cookies existent déjà
    const existingPrefs = getCookie('cookie_preferences');
    
    // Affiche le popup uniquement si aucune préférence n'a été enregistrée
    if (!existingPrefs) {
        popup.classList.remove('hidden');
    }

    // Bouton "Accepter tout"
    if (acceptAllBtn) {
        acceptAllBtn.addEventListener('click', () => {
            const prefs = {
                essential: true,
                analytics: true,
                marketing: true
            };
            setCookie('cookie_preferences', JSON.stringify(prefs), 365); // Expiration : 1 an
            popup.classList.add('hidden');
            preferences.classList.add('hidden');
            console.log('Tous les cookies acceptés');
        });
    }

    // Bouton "Personnaliser"
    if (customizeBtn) {
        customizeBtn.addEventListener('click', () => {
            popup.classList.add('hidden');
            preferences.classList.remove('hidden');
        });
    }

    // Soumission du formulaire de préférences
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const analytics = document.getElementById('analytics').checked;
            const marketing = document.getElementById('marketing').checked;

            const prefs = {
                essential: true,
                analytics: analytics,
                marketing: marketing
            };

            setCookie('cookie_preferences', JSON.stringify(prefs), 365); // Expiration : 1 an
            preferences.classList.add('hidden');
            console.log('Préférences cookies enregistrées :', prefs);
        });
    }

    // Initialisation des services en fonction des préférences
    if (existingPrefs) {
        try {
            const prefs = JSON.parse(existingPrefs);
            
            console.log('Préférences existantes :', prefs);

            if (prefs.analytics) {
                console.log('Analytics activé');
                // Initialiser Google Analytics ici
            }

            if (prefs.marketing) {
                console.log('Marketing activé');
                // Initialiser Facebook Pixel ici
            }

        } catch (e) {
            console.error('Erreur lors de la lecture des préférences des cookies', e);
        }
    }
}

// Initialiser le gestionnaire des cookies après le chargement de la page
document.addEventListener('DOMContentLoaded', handleCookiePreferences);
