<?php
session_start();
include('includes/db.php');

// Vérifier si l'utilisateur est un admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category = trim($_POST['category']);

    // Vérification du fichier image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            die("Type de fichier non autorisé. Veuillez télécharger une image JPG, PNG, GIF ou WebP.");
        }

        // Déplacer l'image dans le dossier uploads/
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $imagePath = 'uploads/' . $imageName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            die("Erreur lors du téléchargement de l'image.");
        }
    } else {
        die("Veuillez sélectionner une image valide.");
    }

    // Insertion du produit en base de données
    try {
        $sql = "INSERT INTO produit (nom, description, prix, stock, image, categorie, date_ajout) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $price, $stock, $imagePath, $category]);
        
        header("Location: admin_products.php?error=1");
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout du produit : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Ajout directement pour l'exemple, à mettre dans ton fichier CSS */
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        form input[type="text"],
        form input[type="number"],
        form input[type="file"],
        form textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fafafa;
            transition: border 0.3s;
        }

        form input[type="text"]:focus,
        form input[type="number"]:focus,
        form input[type="file"]:focus,
        form textarea:focus {
            border: 1px solid #007BFF;
            background-color: #fff;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .container {
                margin: 30px 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    <div class="container">
        <h2>Ajouter un Produit</h2>
        <form action="add_produit.php" method="POST" enctype="multipart/form-data">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" required>

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4" required></textarea>

            <label for="price">Prix (€)</label>
            <input type="number" step="0.01" name="price" id="price" required>

            <label for="stock">Stock</label>
            <input type="number" name="stock" id="stock" required>

            <label for="category">Catégorie</label>
            <input type="text" name="category" id="category" required>

            <label for="image">Image</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <button type="submit">Ajouter le Produit</button>
        </form>
    </div>
</body>
</html>
