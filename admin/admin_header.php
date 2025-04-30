<?php
 
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de Bord</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --border-color: #e5e7eb;
            --text-color: #111827;
            --background-color: #f9fafb;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: var(--background-color);
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 16px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            color: #ffffff;
        }

        .main-content {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            height: 70px;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .header h1 {
            font-size: 24px;
            color: var(--text-color);
        }

        .header .btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .header .btn:hover {
            background: #4338ca;
        }

        .content {
            padding: 30px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AdminPanel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_produit.php">Ajouter Produit</a>
        <a href="view_users.php">Gérer Utilisateurs</a>
        <a href="logout.php">Déconnexion</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Bienvenue, Admin</h1>
            <a href="logout.php" class="btn">Déconnexion</a>
        </div>

        <div class="content">
            <p>Voici votre espace administrateur. Sélectionnez une action dans la barre latérale.</p>
            <!-- Tu peux ajouter ici ton tableau, graphique, ou tout autre contenu -->
        </div>
    </div>

</body>
</html>
