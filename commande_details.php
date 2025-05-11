<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_commande']) || !is_numeric($_GET['id_commande'])) {
    echo "Commande introuvable.";
    exit();
}

$id_commande = (int) $_GET['id_commande'];

try {
    // Get order details
    $stmt = $pdo->prepare("CALL GetCommandeDetails(?)");
    $stmt->execute([$id_commande]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the total from the second result set
    $stmt->nextRowset();
    $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_commande = $totalRow['total_global'] ;
    
    // Get order status
    $stmt = $pdo->prepare("SELECT statut FROM commande WHERE id_commande = ?");
    $stmt->execute([$id_commande]);
    $orderStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Debug function to see what's in the image path
function debug_image_path($image) {
    if (!empty($image)) {
        return $image;
    }
    return ""; // Fallback image
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la commande</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 0;
        }

        .order-details-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .order-details-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .order-status-cancelled {
            background-color: #ffebe6;
            color: #d9534f;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .order-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .order-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1 1 300px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            display: flex;
            flex-direction: column;
        }

        .item-details h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .item-details p {
            margin: 4px 0;
            font-size: 14px;
        }

        .order-summary {
            margin-top: 30px;
            text-align: right;
            font-weight: 600;
            font-size: 18px;
        }

        .back-to-history {
            text-align: center;
            margin-top: 30px;
        }

        .back-to-history a {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .back-to-history a:hover {
            background-color: #555;
        }

        @media (max-width: 768px) {
            .order-items {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="order-details-container">
        <h1>Commande #<?= htmlspecialchars($id_commande) ?></h1>
        <?php if ($orderStatus && $orderStatus['statut'] == 'annulee'): ?>
            <div class="order-status-cancelled">Commande Annulée</div>
        <?php endif; ?>

        <?php if (count($details) > 0): ?>
            <div class="order-items">
                <?php foreach ($details as $item): ?>
                    <div class="order-item">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['produit']) ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                                <span>Pas d'image</span>
                            </div>
                        <?php endif; ?>
                        <div class="item-details">
                            <h2><?= htmlspecialchars($item['produit']) ?></h2>
                            <p>Quantité: <?= htmlspecialchars($item['quantite']) ?></p>
                            <p>Prix unitaire: <?= number_format((float)$item['prix_unitaire'], 2, ',', ' ') ?> dzd</p>
                            <p>Total: <?= number_format((float)$item['total_par_produit'], 2, ',', ' ') ?> dzd</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="order-summary">
                <h3>Total de la commande:</h3>
                <p><?= number_format((float)$total_commande, 2, ',', ' ') ?> €</p>
            </div>
        <?php else: ?>
            <p>Aucun produit trouvé pour cette commande.</p>
        <?php endif; ?>

        <div class="back-to-history">
            <a href="history.php">← Retour à l'historique</a>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>