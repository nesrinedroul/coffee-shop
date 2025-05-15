<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Déconnexion - Coffee Shop</title>
  <meta http-equiv="refresh" content="5;url=index.php"> <!-- Redirection automatique après 5 secondes -->
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #b87b4a, #f7e3c0);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #4e342e;
    }

    .logout-container {
      background-color: #f4e1d2;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.15);
      text-align: center;
      max-width: 450px;
      position: relative;
      overflow: hidden;
    }

    .logout-container::before {
      content: "";
      background-image: url('https://i.imgur.com/OvMZBs9.png'); /* Icône café */
      background-size: 100px;
      background-repeat: no-repeat;
      position: absolute;
      top: -20px;
      left: -20px;
      width: 100px;
      height: 100px;
      opacity: 0.1;
      transform: rotate(-15deg);
    }

    .logout-container h1 {
      font-size: 32px;
      margin-bottom: 20px;
      color: #5d4037;
      font-weight: bold;
    }

    .logout-container p {
      font-size: 18px;
      margin-bottom: 30px;
      color: #6d4c41;
    }

    .btn {
      padding: 12px 30px;
      background-color: #8d6e63;
      color: #fff;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s, transform 0.3s;
    }

    .btn:hover {
      background-color: #6d4c41;
      transform: scale(1.05);
    }

    .steam {
      position: absolute;
      width: 20px;
      height: 100px;
      background: linear-gradient(to bottom, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
      top: -40px;
      left: 50%;
      transform: translateX(-50%);
      opacity: 0.7;
      animation: float 3s infinite;
      border-radius: 50%;
    }

    @keyframes float {
      0% {
        transform: translateX(-50%) translateY(0);
        opacity: 0.7;
      }
      50% {
        opacity: 1;
      }
      100% {
        transform: translateX(-50%) translateY(-80px);
        opacity: 0;
      }
    }

  </style>
</head>
<body>
  <div class="logout-container">
    
    <h1>☕ Vous êtes déconnecté</h1>
    <p>Merci pour votre visite. Vous serez redirigé vers la page d'accueil dans quelques secondes...</p>
    <a href="index.php" class="btn">Retourner maintenant</a>
  </div>
</body>
</html>
