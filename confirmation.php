<?php
session_start();
$commande_id = $_GET['commande'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande confirmée</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Raleway', sans-serif;
            background-color: #fef9f4;
            color: #3e2c1c;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            background-color: #fff7ed;
            border: 2px solid #e6d3b3;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: #6f4e37;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
            background-color: #c89f6a;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        a:hover {
            background-color: #a97e53;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 500px) {
            .container {
                width: 90%;
                padding: 30px 20px;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Merci pour votre commande !</h1>
        <?php if ($commande_id): ?>
            <p>Votre commande a bien été enregistrée.</p>
        <?php else: ?>
            <p>Votre commande a bien été enregistrée.</p>
        <?php endif; ?>
        <a href="index.php">Retour à l'accueil</a>
    </div>
</body>
</html>
