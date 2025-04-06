<?php
session_start();
include('includes/db.php');

// Only allow admin users to access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form inputs
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $image = $_FILES['image']['name'];  // Get the image file name
    $imageTmp = $_FILES['image']['tmp_name'];  // Get the temporary file name

    // Move the uploaded image to a designated folder
    $imagePath = 'uploads/' . $image;
    move_uploaded_file($imageTmp, $imagePath);

    // Insert product into the database
    $sql = "INSERT INTO produit (nom, description, prix, stock, image, categorie, date_ajout) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $description, $price, $stock, $imagePath, $category]);

    echo "Produit ajouté avec succès!";
    header("Location: admin_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Ajouter un Produit</h2>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <label for="name">Nom</label>
            <input type="text" name="name" required><br><br>

            <label for="description">Description</label>
            <textarea name="description" required></textarea><br><br>

            <label for="price">Prix (€)</label>
            <input type="number" step="0.01" name="price" required><br><br>

            <label for="stock">Stock</label>
            <input type="number" name="stock" required><br><br>

            <label for="category">Catégorie</label>
            <input type="text" name="category" required><br><br>

            <label for="image">Image</label>
            <input type="file" name="image" required><br><br>

            <button type="submit">Ajouter le Produit</button>
        </form>
    </div>
</body>
</html>
