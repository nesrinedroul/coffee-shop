<?php
session_start();
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'];

    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT); 
    $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, date_inscription)
            VALUES (:nom, :prenom, :email, :mot_de_passe, 'client', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mot_de_passe', $hashed_password);  

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="./assets/css/styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Créer un compte</h2>

        <form action="register.php" method="POST">
            <label for="nom">Nom</label>
            <input type="text" name="nom" required><br><br>

            <label for="prenom">Prénom</label>
            <input type="text" name="prenom" required><br><br>

            <label for="email">Email</label>
            <input type="email" name="email" required><br><br>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" name="mot_de_passe" required><br><br>

            <label for="confirm_mot_de_passe">Confirmer le mot de passe</label>
            <input type="password" name="confirm_mot_de_passe" required><br><br>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html>
