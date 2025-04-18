<?php
session_start();
include('includes/db.php');

// Check if the user has already accepted cookies
if (!isset($_COOKIE['cookies_accepted'])) {
    $showCookiePopup = true;
} else {
    $showCookiePopup = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse e-mail invalide.";
        header("Location: login.php");
        exit();
    }

    $sql = "SELECT * FROM utilisateur WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['role'] = $user['role'];

        // Set cookies for the user
        setcookie('user_id', $user['id_utilisateur'], time() + (86400 * 30), "/"); // Cookie for 30 days
        setcookie('username', $user['nom'] . ' ' . $user['prenom'], time() + (86400 * 30), "/"); // Cookie for 30 days

        if ($user['role'] === 'admin') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: user/client_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: login.php");
        exit();
    }
}

// If the user has not accepted cookies, show the popup
if ($showCookiePopup) {
    echo "<script>window.onload = function() { document.getElementById('cookie-consent-popup').style.display = 'block'; };</script>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 2em;
            background-color: #f3f3f3;
            border-radius: 10px;
        }
        input, button {
            width: 100%;
            padding: 0.7em;
            margin-bottom: 1em;
        }
        h2 {
            text-align: center;
        }

        /* Cookie Consent Popup Styles */
        #cookie-consent-popup {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px;
            display: none;
            text-align: center;
        }
        #cookie-consent-popup button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        #cookie-consent-popup button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Se connecter</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color:red"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label>Email</label>
            <input type="email" name="email" required><br><br>

            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required><br><br>

            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
    </div>

    <!-- Cookie Consent Popup -->
    <div id="cookie-consent-popup">
        <p>Nous utilisons des cookies pour améliorer votre expérience. En continuant à naviguer, vous acceptez notre politique de cookies.</p>
        <button id="accept-cookies-btn">Accepter</button>
    </div>

    <!-- Cookie Consent Logic -->
    <script>
        // When the user clicks "Accept"
        document.getElementById('accept-cookies-btn').addEventListener('click', function() {
            // Set a cookie to remember the user's consent
            document.cookie = "cookies_accepted=true; path=/; max-age=" + (60 * 60 * 24 * 365); // Cookie for 1 year
            document.getElementById('cookie-consent-popup').style.display = 'none';
        });
    </script>
</body>
</html>
