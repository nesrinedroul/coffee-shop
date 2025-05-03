// assets/js/cookies.js

// Cookie function
function setCookie(name, value, days = 0.05) {
  const expires = new Date(Date.now() + days * 0.0002).toUTCString();
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

// Initialize cookie consent when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  const popup = document.getElementById('cookie-consent-popup');
  const preferences = document.getElementById('cookie-preferences');
  const customizeBtn = document.getElementById('customize-btn');
  const acceptAllBtn = document.getElementById('accept-all-btn');
  const form = document.getElementById('cookie-form');

  // Check if cookie preferences exist
  const existingPrefs = getCookie('cookie_preferences');
  
  // Only show popup if no preferences are set
  if (!existingPrefs) {
      popup.classList.remove('hidden');
  }

  // Accept all cookies
  acceptAllBtn.addEventListener('click', () => {
      const prefs = {
          essential: true,
          analytics: true,
          marketing: true
      };
      setCookie('cookie_preferences', JSON.stringify(prefs));
      popup.classList.add('hidden');
      preferences.classList.add('hidden');
      
      // Here you would initialize all cookie-based services
      console.log('All cookies accepted');
  });

  // Show customization options
  customizeBtn.addEventListener('click', () => {
      popup.classList.add('hidden');
      preferences.classList.remove('hidden');
  });

  // Save customized preferences
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
      preferences.classList.add('hidden');
      
      // Initialize services based on preferences
      if (analytics) {
          console.log('Analytics cookies enabled');
          // Initialize Google Analytics, etc.
      }
      if (marketing) {
          console.log('Marketing cookies enabled');
          // Initialize Facebook Pixel, etc.
      }
  });

  // If preferences exist, initialize services accordingly
  if (existingPrefs) {
      try {
          const prefs = JSON.parse(existingPrefs);
          if (prefs.analytics) {
              console.log('Initializing analytics from saved preferences');
              // Initialize analytics services
          }
          if (prefs.marketing) {
              console.log('Initializing marketing from saved preferences');
              // Initialize marketing services
          }
      } catch (e) {
          console.error('Error parsing cookie preferences', e);
      }
  }
});