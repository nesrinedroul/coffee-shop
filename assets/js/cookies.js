document.addEventListener("DOMContentLoaded", function () {
    // Utility functions
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    const consentPopup = document.getElementById('cookie-consent-popup');
    const preferencesPopup = document.getElementById('cookie-preferences');

    // Show popup only if cookie not set
    if (!getCookie('cookie_preferences')) {
        consentPopup.classList.remove('hidden');
    }

    // Handle "Accept All"
    document.getElementById('accept-all-btn').addEventListener('click', function () {
        const preferences = {
            essential: true,
            analytics: true,
            marketing: true
        };
        setCookie('cookie_preferences', JSON.stringify(preferences), 180); // 180 days
        consentPopup.classList.add('hidden');
    });

    // Handle "Customize"
    document.getElementById('customize-btn').addEventListener('click', function () {
        consentPopup.classList.add('hidden');
        preferencesPopup.classList.remove('hidden');
    });

    // Handle preference form submission
    document.getElementById('cookie-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const analytics = document.getElementById('analytics').checked;
        const marketing = document.getElementById('marketing').checked;

        const preferences = {
            essential: true,
            analytics: analytics,
            marketing: marketing
        };

        setCookie('cookie_preferences', JSON.stringify(preferences), 180);
        preferencesPopup.classList.add('hidden');
    });
});
