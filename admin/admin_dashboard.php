<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied. You must be an admin to view this page.";
    header('Location: ../login.php'); 
    exit();
}

include '../includes/db.php'; 
include '../includes/functions.php';
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt_products = $pdo->query("SELECT COUNT(*) as total_products FROM produit WHERE stock > 0");
$products_count = $stmt_products->fetch()['total_products'];

$stmt_orders = $pdo->query("SELECT COUNT(*) as pending_orders FROM commande WHERE statut = 'En attente'");
$pending_orders = $stmt_orders->fetch()['pending_orders'];


$stmt_users = $pdo->query("SELECT COUNT(*) as active_users FROM utilisateur");
$active_users = $stmt_users->fetch()['active_users'];


$stmt_recent_orders = $pdo->query("SELECT id_commande, 
                                         id_utilisateur, 
                                         date_commande, 
                                         statut, 
                                         total 
                                  FROM commande order by date_commande DESC
                                  LIMIT 5");
$recent_orders = $stmt_recent_orders->fetchAll();
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
            <div class="stats-grid">
                <div class="stat-card">
                    <i class='bx bx-package'></i>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo $products_count; ?></span>
                        <span class="stat-label">Produits en Stock</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <i class='bx bx-cart'></i>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo $pending_orders; ?></span>
                        <span class="stat-label">Commandes en Attente</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <i class='bx bx-user'></i>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo $active_users; ?></span>
                        <span class="stat-label">Utilisateurs Actifs</span>
                    </div>
                </div>
            </div>

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
                </div>
            </div>
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
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id_commande']; ?></td>
                                <td><?php echo $order['id_utilisateur']; ?></td>
                                <td><?php echo $order['date_commande']; ?></td>
                                <td><span class="status <?php echo strtolower(str_replace(' ', '-', $order['statut'])); ?>"><?php echo $order['statut']; ?></span></td>
                                <td>DZD<?php echo number_format($order['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Aucune commande récente</td>
                            </tr>
                        <?php endif; ?>
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