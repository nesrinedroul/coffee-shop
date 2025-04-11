<?php
session_start();
include('includes/db.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $sql = "SELECT * FROM utilisateur WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: user/client_dashboard.php");
            }
            exit();
        } else {
            echo "Mot de passe invalide!";
        }
    } else {
        echo "Utilisateur non trouvé!";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Se connecter</h2>

        <form action="login.php" method="POST">
            <label for="email">Email</label>
            <input type="email" name="email" required><br><br>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" name="mot_de_passe" required><br><br>

            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore de compte? <a href="register.php">Inscrivez-vous ici</a></p>
    </div>
</body>
</html>
