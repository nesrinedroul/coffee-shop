<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied. You must be an admin to view this page.";
    header('Location: ../login.php'); 
    exit();
}

require '../includes/db.php'; 
include '../includes/functions.php';
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de Bord</title>
    <link rel="stylesheet" href="../assets/css/admin_dash.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
    :root {
    --primary-color: #4f46e5;
    --border-color: #e5e7eb;
    --text-color: #111827;
    --background-color: #f9fafb;
    --success: #10b981;
    --warning: #f59e0b;
    --error: #ef4444;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.stat-card i {
    font-size: 2rem;
    color: var(--primary-color);
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-color);
}

.stat-label {
    color: #6b7280;
    font-size: 0.9rem;
}

/* Quick Actions */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    color: var(--text-color);
    transition: transform 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.action-card:hover {
    transform: translateY(-3px);
}

.action-card i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    display: block;
}

/* Recent Orders */
.recent-orders {
    margin-top: 2rem;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

th {
    background-color: var(--background-color);
    font-weight: 600;
}

.status {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status.pending {
    background-color: #fef3c7;
    color: #d97706;
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        width: 100%;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
.system-alerts {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert {
    padding: 10px;
    margin: 10px 0;
    border-left: 4px solid;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alert.unread {
    border-color: #ff4444;
    background: #fff6f6;
}

.alert.read {
    border-color: #ccc;
    opacity: 0.7;
}

.alert-type {
    font-weight: bold;
    min-width: 80px;
    text-transform: uppercase;
}

.alert-content {
    flex-grow: 1;
    margin: 0 15px;
}

.alert-time {
    font-size: 0.9em;
    color: #666;
}
        </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    
    <div class="main-container">
        <div class="header">
            <h1>Tableau de Bord Administrateur</h1>
            <div class="user-info">
                <span>Bienvenue, <?php echo $user['prenom']; ?></span>
            </div>
        </div>

        <div class="main-container">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class='bx bx-package'></i>
                    <div class="stat-content">
                        <span class="stat-value">152</span>
                        <span class="stat-label">Produits en Stock</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <i class='bx bx-cart'></i>
                    <div class="stat-content">
                        <span class="stat-value">24</span>
                        <span class="stat-label">Commandes en Attente</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <i class='bx bx-user'></i>
                    <div class="stat-content">
                        <span class="stat-value">89</span>
                        <span class="stat-label">Utilisateurs Actifs</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Actions Rapides</h2>
                <div class="action-grid">
                    <a href="admin_produit.php" class="action-card">
                        <i class='bx bx-plus-circle'></i>
                        <span>Ajouter un Produit</span>
                    </a>
                    
                    <a href="admin_commande.php" class="action-card">
                        <i class='bx bx-list-ul'></i>
                        <span>Voir les Commandes</span>
                    </a>
                    
                    <a href="admin_commande.php" class="action-card">
                        <i class='bx bx-group'></i>
                        <span>Gérer les Utilisateurs</span>
                    </a>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="recent-orders">
                <h2>Commandes Récentes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Commande</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example row - replace with PHP loop -->
                        <tr>
                            <td>#1234</td>
                            <td>Jean Dupont</td>
                            <td>2023-08-15</td>
                            <td><span class="status pending">En attente</span></td>
                            <td>€149.99</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
<div class="system-alerts">
    <h3>Alertes Système</h3>
    <?php
    $messages = getSystemMessages($pdo);
    foreach ($messages as $msg) {
        $class = $msg['lu'] ? 'read' : 'unread';
        echo "<div class='alert {$class}' data-id='{$msg['id_message']}'>
                <span class='alert-type'>{$msg['type_message']}</span>
                <span class='alert-content'>{$msg['contenu']}</span>
                <span class='alert-time'>{$msg['date_creation']}</span>
              </div>";
    }
    ?>
</div>
    </div>
    <script>
        document.querySelectorAll('.alert.unread').forEach(alert => {
    alert.addEventListener('click', function() {
        const messageId = this.dataset.id;
        fetch('mark_read.php?id=' + messageId)
            .then(() => this.classList.replace('unread', 'read'));
    });
});
    </script>
</body>
</html>