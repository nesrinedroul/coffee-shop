<?php
session_start();
include('../includes/db.php');

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$sql = "DELETE FROM produit WHERE id_produit = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);

echo "Produit supprimé avec succès!";
header("Location: admin_products.php");
exit();
?>
