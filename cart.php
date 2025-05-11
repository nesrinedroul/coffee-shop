<?php
session_start();
require 'includes/db.php';

// Gestion des erreurs et messages
$error = '';
if (isset($_SESSION['error'])) {
    $error = '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Suppression d'un produit
if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['success'] = "Produit retiré du panier";
    }
    header("Location: cart.php");
    exit();
}

if (isset($_POST['update_quantity'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = min(1, max(20), (int)$_POST['quantity']);

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        $_SESSION['success'] = "Quantité mise à jour";
    }
    header("Location: cart.php");
    exit();
}

// Calcul du panier
$totalPrice = 0;
$cartItems = [];
$itemCount = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = rtrim(str_repeat('?,', count($productIds)), ',');
    
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $productId = $product['id_produit'];
        $quantity = $_SESSION['cart'][$productId]['quantity'];
        $itemCount += $quantity;
        $subtotal = $product['prix'] * $quantity;
        $totalPrice += $subtotal;
        
        $cartItems[] = [
            'id' => $productId,
            'image' => $product['image'],
            'name' => $product['nom'],
            'price' => $product['prix'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
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
   
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="assets/css/cart.css">
  

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <main class="container">
        <?php 
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        echo $error; 
        ?>

        <div class="cart-header">
            <h1 class="cart-title">Votre Panier</h1>
            <?php if (!empty($cartItems)): ?>
                <span class="cart-count"><?= $itemCount ?> article<?= $itemCount > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($cartItems)): ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                             <img src="<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="product-image"
                                     onerror="this.src='assets/images/default-product.jpg'">
                            
                            <div class="product-info">
                                <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
                                <span class="product-price"><?= number_format($item['price'], 2) ?>€</span>
                            </div>
                            
                            <div class="quantity-control">
                                <form method="POST" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="button" class="quantity-btn minus" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.parentNode.submit()">-</button>
                                    <input type="number" 
                                           name="quantity" 
                                           class="quantity-input"
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="20"
                                           onchange="this.form.submit()">
                                    <button type="button" class="quantity-btn plus" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.parentNode.submit()">+</button>
                                </form>
                            </div>
                            
                            <div class="product-subtotal">
                                <?= number_format($item['subtotal'], 2) ?>€
                            </div>
                            
                            <a href="cart.php?remove=<?= $item['id'] ?>" class="remove-btn">
                                <i class='bx bx-trash'></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title">Récapitulatif</h3>
                    
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span><?= number_format($totalPrice, 2) ?>€</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span>Gratuite</span>
                    </div>
                    
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span><?= number_format($totalPrice, 2) ?>€</span>
                    </div>
                    
                    <form action="checkout.php" method="POST">
                        <button type="submit" class="checkout-btn">
                            <i class='bx bx-credit-card'></i>
                            Passer la commande
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class='bx bx-cart empty-icon'></i>
                <p class="empty-text">Votre panier est vide</p>
                <a href="produit.php" class="continue-btn">
                    <i class='bx bx-store'></i>
                    Voir nos produits
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include('includes/footer.php'); ?>

    <script>
        // Animation pour les boutons de quantité
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });
    </script>
</body>
</html>