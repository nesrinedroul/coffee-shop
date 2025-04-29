<!-- CONTINUATION DU CODE PHP EXISTANT -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .main-container {
            display: flex;
            justify-content: center;
            padding: 60px 20px;
        }

        .form-wrapper {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .form-wrapper h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #444;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fefefe;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #007BFF;
            outline: none;
            background-color: #fff;
        }

        textarea {
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .form-wrapper {
                padding: 25px 20px;
            }
        }
    </style>
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
