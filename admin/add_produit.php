<!-- CONTINUATION DU CODE PHP EXISTANT -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
    <link href="../assets/css/add_produit.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php include('admin_header.php'); ?>
    <div class="main-container">
        <div class="form-wrapper">
            <h2>Ajouter un Nouveau Produit</h2>
            <form action="add_produit.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="name">Nom du produit</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Prix (€)</label>
                    <input type="number" name="price" id="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="stock">Quantité en stock</label>
                    <input type="number" name="stock" id="stock" required>
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <input type="text" name="category" id="category" required>
                </div>

                <div class="form-group">
                    <label for="image">Image du produit</label>
                    <input type="file" name="image" id="image" accept="image/*" required>
                </div>

                <button type="submit" class="submit-btn">Ajouter le Produit</button>
            </form>
        </div>
    </div>
</body>
</html>
