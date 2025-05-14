<?php
session_start();
include('includes/db.php');

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produit non trouvé.";
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "Produit introuvable.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produit'])) {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }

    $productId = (int) $_POST['id_produit'];
    $quantity = (int) $_POST['quantite'];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity
        ];
    }
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom']) ?> - Coffee Bliss</title>
    <link rel="stylesheet" href="assets/css/details.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<main class="product-detail-container">
    <div class="product-image">
        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
        <?php if ($produit['stock'] > 10): ?>
            <span class="status-badge in-stock"><i class="fas fa-check"></i> En stock</span>
        <?php elseif ($produit['stock'] > 0): ?>
            <span class="status-badge low-stock"><i class="fas fa-exclamation"></i> Stock limité</span>
        <?php else: ?>
            <span class="status-badge out-of-stock"><i class="fas fa-times"></i> Rupture</span>
        <?php endif; ?>
    </div>
    
    <div class="product-info">
        <h1><?= htmlspecialchars($produit['nom']) ?></h1>
        
        <div class="meta-info">
            <span class="category"><?= htmlspecialchars($produit['categorie']) ?></span>
            <span class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                (24 avis)
            </span>
        </div>
        
        <p class="description"><?= nl2br(htmlspecialchars($produit['description'])) ?></p>
        
        <div class="price-section">
            <p class="price"><?= number_format($produit['prix'], 2) ?> €</p>
            <p class="shipping"><i class="fas fa-truck"></i> Livraison gratuite</p>
        </div>
        
        <?php if (isset($_SESSION['username'])): ?>
            <form method="post" class="add-to-cart-form">
                <div class="quantity-selector">
                    <label for="quantite">Quantité :</label>
                    <div class="quantity-control">
                        <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantite" id="quantite" value="1" min="1" max="<?= $produit['stock'] ?>">
                        <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                <button type="submit" class="btn-ajouter">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </button>
            </form>
        <?php else: ?>
            <p class="warning-message">
                <i class="fas fa-info-circle"></i> Veuillez <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">vous connecter</a> pour commander ce produit.
            </p>
        <?php endif; ?>
        
        <div class="product-details">
            <h3><i class="fas fa-info-circle"></i> Détails du produit</h3>
            <ul>
                <li><strong>Origine :</strong> <?= htmlspecialchars($produit['origine'] ?? 'Non spécifiée') ?></li>
                <li><strong>Torréfaction :</strong> <?= htmlspecialchars($produit['torrefaction'] ?? 'Moyenne') ?></li>
                <li><strong>Stock disponible :</strong> <?= $produit['stock'] ?> unités</li>
            </ul>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>

<script>
// Gestionnaire de quantité
document.querySelectorAll('.qty-btn').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        let value = parseInt(input.value);
        
        if (button.classList.contains('minus') && value > 1) {
            input.value = value - 1;
        } else if (button.classList.contains('plus') && value < <?= $produit['stock'] ?>) {
            input.value = value + 1;
        }
    });
});
</script>
</body>
</html>