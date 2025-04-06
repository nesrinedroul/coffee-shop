<?php
session_start();
include('includes/db.php');

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Derniers 4 produits
$stmtLatest = $pdo->prepare("SELECT * FROM produit ORDER BY id_produit DESC LIMIT 4");
$stmtLatest->execute();
$latestProducts = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

// Tous les produits ou recherche
$sql = "SELECT * FROM produit WHERE nom LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%"]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Coffee Bliss ☕</title>
    <link rel="stylesheet" href="assets/css/index.css">
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

<!-- HERO SECTION -->
<section class="hero">
    <h2>☕ Bienvenue chez Coffee Bliss</h2>
    <p>Un café qui réveille vos sens – fraîcheur, passion et arômes d’exception</p>
    <a href="#products" class="btn-hero">Voir nos cafés</a>
</section>

<!-- SEARCH -->
<section class="search-section">
    <form action="index.php" method="get">
        <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">🔍</button>
    </form>
</section>

<!-- DERNIERS PRODUITS -->
<section class="latest-products">
    <h2>Nouveautés</h2>
    <div class="products">
        <?php foreach ($latestProducts as $produit): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <span class="badge new">Nouveau</span>
                <p><?= htmlspecialchars(substr($produit['description'], 0, 60)) ?>...</p>
                <span class="price"><?= $produit['prix'] ?> €</span>
                <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- TOUS LES PRODUITS -->
<section id="products">
    <h2>Nos Produits</h2>
    <div class="products">
        <?php if ($produits): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" alt="Produit" class="product-image">
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars($produit['description']) ?></p>
                    <span class="price"><?= $produit['prix'] ?> €</span>
                    <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir Détail</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé.</p>
        <?php endif; ?>
    </div>
</section>

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
