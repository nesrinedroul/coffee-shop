<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Déconnexion</title>
  <meta http-equiv="refresh" content="5;url=../index.php"> <!-- Redirection automatique après 5 secondes -->
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #74ebd5, #9face6);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #333;
    }
    .logout-container {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 400px;
    }
    .logout-container h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }
    .logout-container p {
      font-size: 16px;
      margin-bottom: 30px;
    }
    .btn {
      padding: 12px 25px;
      background-color: #6c63ff;
      color: white;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 16px;
      transition: background-color 0.3s;
    }
    .btn:hover {
      background-color: #574b90;
    }
  </style>
</head>
<body>
  <div class="logout-container">
    <h1>Vous êtes déconnecté</h1>
    <p>Merci pour votre visite. Vous allez être redirigé vers la page d’accueil dans quelques secondes...</p>
    <a href="index.php" class="btn">Retourner maintenant</a>
  </div>
</body>
</html>
