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
        
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }
        
        .confirmation-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .confirmation-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }
        
        .confirm-btn {
            background-color: #d33;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include('admin_header.php'); ?>


<div class="notifications-container">
    <?php foreach ($lowStock as $product): ?>
        <div class="stock-notification">
            <i class='bx bx-error-circle'></i>
            <span>Stock faible pour <strong><?php echo htmlspecialchars($product['nom']); ?></strong>: <?php echo $product['stock']; ?> restants</span>
            <span class="close-btn">&times;</span>
        </div>
    <?php endforeach; ?>
</div>

<div class="confirmation-dialog" id="confirmationDialog">
    <div class="confirmation-box">
        <h3>Confirmer la suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.</p>
        <div class="confirmation-buttons">
            <button class="cancel-btn" id="cancelDelete">Annuler</button>
            <button class="confirm-btn" id="confirmDelete">Supprimer</button>
        </div>
    </div>
</div>

<div class="main-content ">
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
                        <img src="../uploads/<? htmlspecialchars($product['name'])?>.jpg" alt="Produit Image">
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
                    <a href="#" class="delete-btn" onclick="showDeleteConfirmation(<?php echo $product['id_produit']; ?>)">Supprimer</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    
    let currentDeleteUrl = '';
    
    function showDeleteConfirmation(productId) {
        currentDeleteUrl = 'delete_produit.php?id=' + productId;
        document.getElementById('confirmationDialog').style.display = 'flex';
    }
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        window.location.href = currentDeleteUrl;
    });
    
    document.getElementById('cancelDelete').addEventListener('click', function() {
        document.getElementById('confirmationDialog').style.display = 'none';
    });
    
   
    document.getElementById('confirmationDialog').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.stock-notification').style.opacity = '0';
            setTimeout(() => {
                e.target.closest('.stock-notification').remove();
            }, 300);
        });
    });

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