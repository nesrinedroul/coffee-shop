function setCookie(name, value, days = 365) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
  }
  
  function getCookie(name) {
    const match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return match ? decodeURIComponent(match[2]) : null;
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('cookie-consent-popup');
    const preferences = document.getElementById('cookie-preferences');
    const customizeBtn = document.getElementById('customize-btn');
    const acceptAllBtn = document.getElementById('accept-all-btn');
    const form = document.getElementById('cookie-form');
    const existingPrefs = getCookie('cookie_preferences');
    if (!existingPrefs) {
      popup.style.display = 'block';
    }
    acceptAllBtn.addEventListener('click', () => {
      const prefs = {
        essential: true,
        analytics: true,
        marketing: true
      };
      setCookie('cookie_preferences', JSON.stringify(prefs));
      popup.style.display = 'none';
      preferences.style.display = 'none';
    });
    customizeBtn.addEventListener('click', () => {
      popup.style.display = 'none';
      preferences.style.display = 'block';
    });
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const analytics = document.getElementById('analytics').checked;
      const marketing = document.getElementById('marketing').checked;
  
      const prefs = {
        essential: true,
        analytics: analytics,
        marketing: marketing
      };
  
      setCookie('cookie_preferences', JSON.stringify(prefs));
      preferences.style.display = 'none';
    });
  });
  