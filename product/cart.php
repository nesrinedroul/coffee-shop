<?php
session_start();
include('../includes/db.php');
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    unset($_SESSION['cart'][$productId]);
    header("Location: cart.php");
    exit();
}
if (isset($_POST['update_quantity'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }
    header("Location: cart.php");
    exit();
}

$totalPrice = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $productId => $cartItem) {
        $sql = "SELECT * FROM produit WHERE id_produit = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $productId]);
        $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPrice += $productDetails['prix'] * $cartItem['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Mon Panier</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Accueil</a></li>
                    <li><a href="produits.php">Produits</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="cart.php">Panier (<span id="cart-count"><?php echo count($_SESSION['cart'] ?? []); ?></span>)</a></li>
                    <li><a href="../login.php">Se connecter</a></li>
                    <li><a href="../register.php">S'inscrire</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="cart">
        <div class="container">
            <h2>Articles dans votre panier</h2>
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Prix Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $productId => $cartItem): 
                            $sql = "SELECT * FROM produit WHERE id_produit = :id";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([':id' => $productId]);
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                            <tr>
                                <td>
                                    <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['nom']; ?>" class="cart-product-image">
                                    <span><?php echo $product['nom']; ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <input type="number" name="quantity" value="<?php echo $cartItem['quantity']; ?>" min="1" max="10">
                                        <button type="submit" name="update_quantity">Mettre à jour</button>
                                    </form>
                                </td>
                                <td><?php echo number_format($product['prix'], 2); ?>€</td>
                                <td><?php echo number_format($product['prix'] * $cartItem['quantity'], 2); ?>€</td>
                                <td><a href="cart.php?remove=<?php echo $productId; ?>" class="remove-item">Supprimer</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-summary">
                    <p><strong>Total: <?php echo number_format($totalPrice, 2); ?>€</strong></p>
                    <a href="checkout.php" class="checkout-btn">Procéder à la commande</a>
                </div>

            <?php else: ?>
                <p>Votre panier est vide. <a href="../user/client_dashboard.php">Retourner aux produits</a></p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Notre Café. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
