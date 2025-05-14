<?php
include('../includes/db.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de Bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary: #6f4e37;
            --primary-light: #8c6a50;
            --primary-dark: #553a28;
            --secondary: #5a4e3c;
            --text: #1a1a1a;
            --text-light: #4b5563;
            --text-lighter: #6b7280;
            --bg: #f9fafb;
            --bg-light: #ffffff;
            --border: #e5e7eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --radius: 0.5rem;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo i {
            font-size: 1.75rem;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            gap: 0.75rem;
        }

        .nav-item i {
            font-size: 1.25rem;
            width: 24px;
            display: flex;
            justify-content: center;
        }

        .nav-item:hover, .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Contenu principal */
        .main-content{
            margin-left: 290px;
            width: calc(100% - 280px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header amélioré */
        .header {
            height: 80px;
            background-color: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: var(--shadow);
        }

        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(111, 78, 55, 0.1);
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 50%;
            background-color: var(--bg-light);
            color: var(--text-light);
            box-shadow: var(--shadow);
        }

        .btn-icon:hover {
            background-color: white;
            color: var(--primary);
        }

        /* Version mobile */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
                transform: translateX(-100%);
                transition: var(--transition);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                
            }
            
            .mobile-menu-btn {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar amélioré -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class='bx bx-coffee'></i>
                <span>CoffeeAdmin</span>
            </div>
        </div>
        
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item active">
                <i class='bx bx-grid-alt'></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_produit.php" class="nav-item">
                <i class='bx bx-package'></i>
                <span>Produits</span>
            </a>
            <a href="admin_commande.php" class="nav-item">
                <i class='bx bx-cart'></i>
                <span>Commandes</span>
            </a>
          
        
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item">
                <i class='bx bx-log-out'></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    
    </div>
</body>
</html>