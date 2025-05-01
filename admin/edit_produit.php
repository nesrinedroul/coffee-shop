<?php

include('../includes/db.php');
if (!isset($_GET['id'])) {
    echo "ID du produit manquant.";
    exit();
}
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

    $image = isset($_FILES['image']['name']) && $_FILES['image']['name'] ? $_FILES['image']['name'] : $product['image'];
    $imageTmp = $_FILES['image']['tmp_name'];

    if (!empty($imageTmp)) {
        $imagePath = 'uploads/' . $image;
        move_uploaded_file($imageTmp, $imagePath);
    } else {
        $imagePath = $product['image'];
    }

    $sql = "UPDATE produit SET nom = ?, description = ?, prix = ?, stock = ?, image = ?, categorie = ? WHERE id_produit = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $description, $price, $stock, $imagePath, $category, $product_id]);

    header("Location: admin_produit.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            background: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .form-left, .form-right {
            flex: 1 1 400px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f9f9f9;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .product-image-preview img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 15px;
            object-fit: cover;
            max-height: 300px;
        }
        button {
            background-color: #4f46e5;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background-color: #4338ca;
        }
        @media(max-width: 768px){
            form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include('admin_header.php'); ?>
    <div class="container main-container main">
        <h2>Modifier le Produit</h2>
        <form action="edit_produit.php?id=<?php echo $product['id_produit']; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-left">
                <label for="name">Nom</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['nom']); ?>" required>

                <label for="description">Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                <label for="price">Prix (DZD)</label>
                <input type="number" step="0.01" name="price" value="<?php echo $product['prix']; ?>" required>

                <label for="stock">Stock</label>
                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>

                <label for="category">Catégorie</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($product['categorie']); ?>" required>
            </div>

            <div class="form-right">
                <div class="product-image-preview">
                    <label>Image Actuelle</label>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Image du produit">
                    <?php else: ?>
                        <img src="default-product.png" alt="Image par défaut">
                    <?php endif; ?>
                </div>

                <label for="image">Changer l'Image</label>
                <input type="file" name="image">

                <button type="submit">Mettre à Jour</button>
            </div>

        </form>
    </div>
</body>
</html>
