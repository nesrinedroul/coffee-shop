<?php
include('../includes/db.php');
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
    <title>Admin - Produits</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin_product.css"> 

</head>
<body>
<?php include('admin_header.php'); ?>
<div class="main-container main-content main">
    <div class="header">
        <h1>Produits</h1>
        <a href="add_produit.php" class="btn">+ Ajouter Produit</a>
    </div>

    <div class="content">
    <?php foreach ($products as $product): ?>
        <div class="card">
            <?php if (!empty($product['image'])): ?>
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Produit Image">
                </div>
            <?php else: ?>
                <div class="product-image">
                    <img src="default-product.png" alt="Produit Image"> <!-- image par défaut si vide -->
                </div>
            <?php endif; ?>

            <div class="card-body">
                <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p>Catégorie: <?php echo htmlspecialchars($product['categorie']); ?></p>
                <p class="price"><?php echo number_format($product['prix'], 2); ?> DZD</p>
                <p>Stock: <?php echo $product['stock']; ?></p>
            </div>

            <div class="actions">
                <a href="edit_produit.php?id=<?php echo $product['id_produit']; ?>">Modifier</a>
                <a href="delete_produit.php?id=<?php echo $product['id_produit']; ?>" class="delete">Supprimer</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</div>

</body>
</html>
s