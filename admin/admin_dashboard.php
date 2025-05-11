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
    <link rel="stylesheet" href="../assets/css/admin._dash.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"> 
</head>
<body>
    <?php include('admin_header.php'); ?>
    
    <div class="main-content">
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