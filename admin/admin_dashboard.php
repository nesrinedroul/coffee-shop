<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied. You must be an admin to view this page.";
    header('Location: login.php'); 
    exit();
}

require '../includes/db.php'; 
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css"> 
</head>
<body>
    <div class="dashboard-container">
        <?php include('admin_header.php'); ?>
        <div class="main-content">
            <h1>Welcome, <?php echo $user['prenom']; ?>!</h1>
            <div class="admin-options">
                <h3>What would you like to do?</h3>
                <div class="option">
                    <h4><a href="manage_users.php">Manage Commands</a></h4>
                    <p>View, edit delete a command.</p>
                </div>
                <div class="option">
                    <h4><a href="admin_produit.php">Manage Products</a></h4>
                    <p>Add or update products in the store.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
