// Check if the user has already accepted cookies
if (!getCookie('cookies_accepted')) {
    document.getElementById('cookie-consent-popup').style.display = 'block';
}

// When the user clicks "Accept"
document.getElementById('accept-cookies-btn').addEventListener('click', function() {
    // Set a cookie to remember the user's consent
    setCookie('cookies_accepted', 'true', 365); // The cookie will expire in 1 year
    document.getElementById('cookie-consent-popup').style.display = 'none';

    // After accepting, you can set the other cookies like user ID, etc.
    // Example: setUserCookies();
});

// Function to set a cookie
function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

// Function to get a cookie value
function getCookie(name) {
    var nameEq = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(nameEq) === 0) return c.substring(nameEq.length, c.length);
    }
    return "";
}
