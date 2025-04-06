<?php
session_start();
include('includes/db.php');

// Only allow admin users to access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch the product from the database
$product_id = $_GET['id'];
$sql = "SELECT * FROM produit WHERE id_produit = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Produit non trouvé!";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $image = $_FILES['image']['name'] ? $_FILES['image']['name'] : $product['image'];
    $imageTmp = $_FILES['image']['tmp_name'];

    if ($imageTmp) {
        $imagePath = 'uploads/' . $image;
        move_uploaded_file($imageTmp, $imagePath);
    } else {
        $imagePath = $product['image'];
    }

    $sql = "UPDATE produit SET nom = ?, description = ?, prix = ?, stock = ?, image = ?, categorie = ? WHERE id_produit = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $description, $price, $stock, $imagePath, $category, $product_id]);

    echo "Produit mis à jour avec succès!";
    header("Location: admin_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Modifier le Produit</h2>
        <form action="edit_product.php?id=<?php echo $product['id_produit']; ?>" method="POST" enctype="multipart/form-data">
            <label for="name">Nom</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['nom']); ?>" required><br><br>

            <label for="description">Description</label>
            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>

            <label for="price">Prix (€)</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['prix']; ?>" required><br><br>

            <label for="stock">Stock</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required><br><br>

            <label for="category">Catégorie</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($product['categorie']); ?>" required><br><br>

            <label for="image">Image</label>
            <input type="file" name="image"><br><br>

            <button type="submit">Mettre à Jour</button>
        </form>
    </div>
</body>
</html>
