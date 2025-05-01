<?php
session_start();
include('../includes/db.php');

$product_id = $_GET['id'];
$sql = "DELETE FROM produit WHERE id_produit = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);

echo "Produit supprimé avec succès!";
header("Location: admin_produit.php");
exit();
?>
