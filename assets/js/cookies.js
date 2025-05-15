
function setCookie(name, value, days = 1) {
    const expires = new Date(Date.now() + days*60*60*24).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}

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

function handleCookiePreferences() {
    const popup = document.getElementById('cookie-consent-popup');
    const preferences = document.getElementById('cookie-preferences');
    const customizeBtn = document.getElementById('customize-btn');
    const acceptAllBtn = document.getElementById('accept-all-btn');
    const form = document.getElementById('cookie-form');

    const existingPrefs = getCookie('cookie_preferences');
    
    if (!existingPrefs) {
        popup.classList.remove('hidden');
    }

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

    if (customizeBtn) {
        customizeBtn.addEventListener('click', () => {
            popup.classList.add('hidden');
            preferences.classList.remove('hidden');
        });
    }

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
    if (existingPrefs) {
        try {
            const prefs = JSON.parse(existingPrefs);
            
            console.log('Préférences existantes :', prefs);

            if (prefs.analytics) {
                console.log('Analytics activé');
            }

            if (prefs.marketing) {
                console.log('Marketing activé');
             
            }

        } catch (e) {
            console.error('Erreur lors de la lecture des préférences des cookies', e);
        }
    }
}

document.addEventListener('DOMContentLoaded', handleCookiePreferences);
