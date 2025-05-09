<?php
session_start();
include('includes/db.php');

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
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="split-container">
        <div class="left-section">
            <h2>Connexion</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <label>Email</label>
                <input type="email" name="email" required>

                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" required>

                <button type="submit">Se connecter</button>
            </form>
            <p class="alternate-action">Mot de passe oublié ? <a href="#">Réinitialiser</a></p>
        </div>

        <div class="right-section">
            <div class="welcome-content">
                <h2>Bienvenue !</h2>
                <p>Si vous n'avez pas encore de compte, inscrivez-vous pour commencer votre expérience.</p>
                <a href="register.php" class="cta-button">Créer un compte</a>
            </div>
        </div>
    </div>
</body>
</html>