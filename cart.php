<?php
session_start();
require 'includes/db.php';

// Gestion des erreurs
$error = '';
if (isset($_SESSION['error'])) {
    $error = '<div class="error-message">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Gestion de la suppression d'article
if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['success'] = "Produit retiré du panier";
    }
    header("Location: cart.php");
    exit();
}

// Mise à jour de la quantité
if (isset($_POST['update_quantity'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, min(20, (int)$_POST['quantity'])); // Limite 1-20

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        $_SESSION['success'] = "Quantité mise à jour";
    }
    header("Location: cart.php");
    exit();
}

// Récupération des détails du panier
$totalPrice = 0;
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    // Récupération de tous les IDs de produits en une seule requête
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = rtrim(str_repeat('?,', count($productIds)), ',');
    
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $productId = $product['id_produit'];
        $quantity = $_SESSION['cart'][$productId]['quantity'];
        $totalPrice += $product['prix'] * $quantity;
        $cartItems[$productId] = [
            'product' => $product,
            'quantity' => $quantity
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - CaféShop</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        .error-message {
            padding: 15px;
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
            border-radius: 5px;
            margin: 20px 0;
        }

        .success-message {
            padding: 15px;
            background: #dfd;
            color: #080;
            border: 1px solid #cfc;
            border-radius: 5px;
            margin: 20px 0;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .cart-table th {
            background: #5c3d2e;
            color: white;
            padding: 12px;
        }

        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .cart-product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .checkout-btn {
            background: #5c3d2e;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .checkout-btn:hover {
            background: #4a3226;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <main class="container">
        <?php 
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        echo $error; 
        ?>

        <h1>Votre Panier</h1>

        <?php if (!empty($cartItems)): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $productId => $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['product']['nom']) ?>" 
                                     class="cart-product-image">
                                <?= htmlspecialchars($item['product']['nom']) ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <input type="number" 
                                           name="quantity" 
                                           class="quantity-input"
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="20">
                                    <button type="submit" name="update_quantity" class="btn">
                                        <i class='bx bx-refresh'></i>
                                    </button>
                                </form>
                            </td>
                            <td><?= number_format($item['product']['prix'], 2) ?>€</td>
                            <td><?= number_format($item['product']['prix'] * $item['quantity'], 2) ?>€</td>
                            <td>
                                <a href="cart.php?remove=<?= $productId ?>" class="text-danger">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Total du panier : <?= number_format($totalPrice, 2) ?>€</h3>
                <form action="checkout.php" method="POST">
                    <button type="submit" class="checkout-btn">
                        <i class='bx bx-credit-card'></i>
                        Passer la commande
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <i class='bx bx-cart' style="font-size: 4rem; color: #5c3d2e;"></i>
                <p>Votre panier est vide</p>
                <a href="produit.php" class="btn">
                    <i class='bx bx-store'></i>
                    Voir nos produits
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include('includes/footer.php'); ?>
</body>
</html>