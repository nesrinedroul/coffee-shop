<?php
session_start();
include('includes/db.php');

// Only allow admin users to access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Delete the product from the database
$product_id = $_GET['id'];
$sql = "DELETE FROM produit WHERE id_produit = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);

echo "Produit supprimé avec succès!";
header("Location: admin_products.php");
exit();
?>
