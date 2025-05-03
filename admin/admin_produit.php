<?php
include('../includes/db.php');
$sql = "SELECT * FROM produit";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$lowStock = $pdo->query("
    SELECT nom, stock 
    FROM produit 
    WHERE stock < 5
    ORDER BY stock ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Produits</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin_product.css">
    <style>
        /* Notification system styles */
        .notifications-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
        }
        
        .stock-notification {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }
        
        .stock-notification i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .stock-notification .close-btn {
            margin-left: auto;
            cursor: pointer;
            color: #856404;
            font-weight: bold;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Main content spacing */
        .main-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php include('admin_header.php'); ?>

<!-- Notifications container -->
<div class="notifications-container">
    <?php foreach ($lowStock as $product): ?>
        <div class="stock-notification">
            <i class='bx bx-error-circle'></i>
            <span>Stock faible pour <strong><?php echo htmlspecialchars($product['nom']); ?></strong>: <?php echo $product['stock']; ?> restants</span>
            <span class="close-btn">&times;</span>
        </div>
    <?php endforeach; ?>
</div>

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
                        <img src="../assets/images/default-product.png" alt="Produit Image">
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
                    <a href="edit_produit.php?id=<?php echo $product['id_produit']; ?>" class="edit-btn">Modifier</a>
                    <a href="delete_produit.php?id=<?php echo $product['id_produit']; ?>" class="delete-btn">Supprimer</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Close notification when X is clicked
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.stock-notification').style.opacity = '0';
            setTimeout(() => {
                e.target.closest('.stock-notification').remove();
            }, 300);
        });
    });
    
    // Auto-close notifications after 8 seconds
    document.querySelectorAll('.stock-notification').forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 8000);
    });
</script>

</body>
</html>