<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied. You must be an admin to view this page.";  // Debugging line
    header('Location: login.php'); // Redirect to login if the user is not an admin
    exit();
}

require '../includes/db.php'; // Include your database connection file
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Debugging output
var_dump($user);  // This will display user data from the database

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css"> <!-- Link your CSS file -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="admin_produit.php">Manage Products</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Welcome, <?php echo $user['prenom']; ?>!</h1>
            <div class="admin-options">
                <h3>What would you like to do?</h3>
                <div class="option">
                    <h4><a href="manage_users.php">Manage Users</a></h4>
                    <p>View, edit, or delete users.</p>
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
