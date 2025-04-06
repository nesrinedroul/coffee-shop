<?php
session_start();
include('includes/db.php');

// Vérification de l'ID dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produit non trouvé.";
    exit;
}

$id = (int) $_GET['id'];

// Requête pour récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucun produit trouvé
if (!$produit) {
    echo "Produit introuvable.";
    exit;
}

// Ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produit'])) {
    $productId = (int) $_POST['id_produit'];
    $quantity = (int) $_POST['quantite'];

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity; // Increment quantity if product is already in the cart
    } else {
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity
        ];
    }
    
    header("Location: cart.php"); // Redirect to cart page
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produit['nom']) ?> - Coffee Bliss</title>
    <link rel="stylesheet" href="assets/css/details.css">
</head>
<body>

<header>
    <h1 class="logo">Coffee Bliss</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="cart.php">🛒 Panier</a>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['user']) ?></span>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php">Créer un compte</a>
        <?php endif; ?>
    </nav>
</header>

<!-- SECTION DÉTAILS PRODUIT -->
<main class="product-detail-container">
    <div class="product-image">
        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
    </div>
    <div class="product-info">
        <h2><?= htmlspecialchars($produit['nom']) ?></h2>
        <p class="description"><?= htmlspecialchars($produit['description']) ?></p>
        <p class="price"><strong>Prix :</strong> <?= $produit['prix'] ?> €</p>

        <form method="post" action="details.php?id=<?= $produit['id_produit'] ?>">
            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
            <label for="quantite">Quantité :</label>
            <input type="number" name="quantite" id="quantite" value="1" min="1">
            <button type="submit" class="btn-ajouter">Ajouter au panier</button>
        </form>
    </div>
</main>

<!-- FOOTER -->
<footer>
    <div class="footer-links">
        <p>📍 Coffee Bliss, Alger - Bab Ezzouar</p>
        <p>📞 +213 660 123 456</p>
        <p>✉ contact@coffeebliss.dz</p>
    </div>
    <p>&copy; <?= date('Y') ?> Coffee Bliss. Tous droits réservés.</p>
</footer>

</body>
</html>
