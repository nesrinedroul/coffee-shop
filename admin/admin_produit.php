<?php
session_start();
include('includes/db.php');

// Only allow admin users to access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");  // Redirect if not admin
    exit();
}

// Fetch all products from the database
$sql = "SELECT * FROM produit";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Produits</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Gestion des Produits</h2>
        <a href="add_product.php" class="btn">Ajouter un Produit</a>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['nom']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo '€' . number_format($product['prix'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo htmlspecialchars($product['categorie']); ?></td>
                        <td>
                            <a href="edit_produit.php?id=<?php echo $product['id_produit']; ?>">Modifier</a> |
                            <a href="delete_produit.php?id=<?php echo $product['id_produit']; ?>">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
