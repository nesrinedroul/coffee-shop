<?php
session_start();
require 'includes/db.php';
$error = '';
$success = '';

if (isset($_SESSION['error'])) {
    $error = '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['success'] = "Produit retiré du panier";
    }
    header("Location: cart.php");
    exit();
}

if (isset($_POST['quantity']) && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    $quantity = max(1, min(20, $quantity));
    try {
        $stmt = $pdo->prepare("SELECT stock FROM produit WHERE id_produit = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $quantity > $product['stock']) {
            $_SESSION['error'] = "Stock insuffisant ! Seulement {$product['stock']} article(s) disponible(s).";
            $quantity = $product['stock']; 
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
            if (!isset($_SESSION['error'])) {
                $_SESSION['success'] = "Quantité mise à jour";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
    
    header("Location: cart.php");
    exit();
}


$totalPrice = 0;
$cartItems = [];
$itemCount = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    if (!empty($productIds)) {
        $placeholders = rtrim(str_repeat('?,', count($productIds)), ',');
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit IN ($placeholders)");
            $stmt->execute($productIds);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                $productId = $product['id_produit'];
                if (isset($_SESSION['cart'][$productId])) {
                    $quantity = $_SESSION['cart'][$productId]['quantity'];
             
                    if ($quantity > $product['stock']) {
                        $_SESSION['error'] = "Attention : La quantité de {$product['nom']} a été ajustée car le stock disponible est de {$product['stock']} article(s).";
                        $quantity = $product['stock'];
                        $_SESSION['cart'][$productId]['quantity'] = $quantity;
                    }
                    
                    $itemCount += $quantity;
                    $subtotal = $product['prix'] * $quantity;
                    $totalPrice += $subtotal;
                    
                    $cartItems[] = [
                        'id' => $productId,
                        'image' => $product['image'],
                        'name' => $product['nom'],
                        'price' => $product['prix'],
                        'quantity' => $quantity,
                        'stock' => $product['stock'],
                        'subtotal' => $subtotal
                    ];
                }
            }
        } catch (PDOException $e) {
            $error = '<div class="alert alert-danger">Erreur lors du chargement du panier : ' . $e->getMessage() . '</div>';
        }
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
        <div id="notification-container"></div>
        
        <?php 
        if ($error) echo $error;
        if ($success) echo $success;
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
                                <span class="product-price"><?= number_format($item['price'], 2) ?>DZD</span>
                                <small class="stock-info"><?= $item['stock'] > 5 ? 'En stock' : ($item['stock'] > 0 ? 'Stock limité: ' . $item['stock'] . ' restant(s)' : 'Rupture de stock') ?></small>
                            </div>
                            
                            <div class="quantity-control">
                                <form action="cart.php" method="POST" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="button" class="quantity-btn minus" onclick="decrementQuantity(this.form, <?= $item['id'] ?>)">-</button>
                                    <input type="number" 
                                           name="quantity" 
                                           class="quantity-input"
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="<?= min(20, $item['stock']) ?>"
                                           data-max-stock="<?= $item['stock'] ?>"
                                           onchange="validateQuantity(this)">
                                    <button type="button" class="quantity-btn plus" onclick="incrementQuantity(this.form, <?= $item['id'] ?>, <?= $item['stock'] ?>)">+</button>
                                </form>
                            </div>
                            
                            <div class="product-subtotal">
                                <?= number_format($item['subtotal'], 2) ?>DZD
                            </div>
                            
                            <a href="cart.php?remove=<?= $item['id'] ?>" class="remove-btn" onclick="return confirm('Voulez-vous vraiment retirer ce produit du panier?')">
                                <i class='bx bx-trash'></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title">Récapitulatif</h3>
                    
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span><?= number_format($totalPrice, 2) ?>DZD</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span>Gratuite</span>
                    </div>
                    
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span><?= number_format($totalPrice, 2) ?>DZD</span>
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
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            container.appendChild(notification);
        
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            }, 5000);
        }
        
        function decrementQuantity(form, productId) {
            const input = form.querySelector('input[name="quantity"]');
            const currentValue = parseInt(input.value, 10);
            
            if (currentValue > 1) {
                input.value = currentValue - 1;
                form.submit();
            }
        }
        
        
        function incrementQuantity(form, productId, maxStock) {
            const input = form.querySelector('input[name="quantity"]');
            const currentValue = parseInt(input.value, 10);
            
            if (currentValue < Math.min(20, maxStock)) {
                input.value = currentValue + 1;
                form.submit();
            } else if (currentValue >= maxStock) {
                showNotification(`Stock insuffisant ! Seulement ${maxStock} article(s) disponible(s).`, 'warning');
            }
        }
        
        // Fonction pour valider la quantité saisie manuellement
        function validateQuantity(input) {
            const maxStock = parseInt(input.getAttribute('data-max-stock'), 10);
            let value = parseInt(input.value, 10);
            
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > Math.min(20, maxStock)) {
                value = Math.min(20, maxStock);
                showNotification(`Quantité ajustée au maximum disponible (${value})`, 'warning');
            }
            
            input.value = value;
            input.form.submit();
        }
        
        // Afficher les messages d'erreur/succès existants sous forme de notifications
        document.addEventListener('DOMContentLoaded', function() {
            const errorAlert = document.querySelector('.alert-danger');
            const successAlert = document.querySelector('.alert-success');
            
            if (errorAlert) {
                showNotification(errorAlert.textContent, 'error');
                errorAlert.style.display = 'none';
            }
            
            if (successAlert) {
                showNotification(successAlert.textContent, 'success');
                successAlert.style.display = 'none';
            }
        });
    </script>
</body>
</html>