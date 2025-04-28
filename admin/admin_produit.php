<?php
session_start();
include('../includes/db.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
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
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #f9fafb;
            --text-color: #1f2937;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--secondary-color);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            width: 220px;
            background: var(--primary-color);
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 40px;
            font-weight: 700;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 16px;
            transition: 0.3s;
        }
        .sidebar a:hover {
            color: #ffffff;
        }
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .header {
            height: 70px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
        }
        .header h1 {
            font-size: 24px;
            color: var(--text-color);
        }
        .header .btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        .header .btn:hover {
            background: #4338ca;
        }
        .content {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: var(--text-color);
        }
        .card p {
            margin-bottom: 6px;
            font-size: 14px;
            color: #6b7280;
        }
        .card .price {
            color: #10b981;
            font-size: 18px;
            margin: 10px 0;
        }
        .card .actions {
            margin-top: 10px;
        }
        .card .actions a {
            margin-right: 10px;
            font-size: 14px;
            padding: 8px 12px;
            background: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .card .actions a.delete {
            background: #ef4444;
        }
        .card .actions a:hover {
            opacity: 0.9;
        }
        .product-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
    border-radius: 8px;
    margin-bottom: 15px;
    background-color: #f3f4f6;
    display: flex;
    justify-content: center;
    align-items: center;
}
.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* remplissage intelligent */
}
.card-body {
    flex: 1;
}

    </style>
</head>
<body>

<div class="sidebar">
    <h2>AdminPanel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_produit.php">Ajouter Produit</a>
    <a href="logout.php">Déconnexion</a>
</div>

<div class="main">
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