<?php
session_start();

include('../includes/db.php');

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM produit WHERE 1";
$params = [];

if ($search) {
    $query .= " AND nom LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND categorie = ?";
    $params[] = $category;
}

$query .= " ORDER BY date_ajout DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon espace - Coffee Ness</title>
    <link rel="stylesheet" href="../assets/css/client_dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>👋 Bienvenue</h2>
        <p><?= htmlspecialchars($_SESSION['user']) ?></p>
        <nav>
            <a href="client_dashboard.php">🏠 Accueil</a>
            <a href="?category=chaude">☕ Chaude</a>
            <a href="?category=cold">🍵 cold</a>
            <a href="?category=pastries">🥐 Pâtisseries</a>
            <a href="product/cart.php">🛒 Mon Panier</a>
            <a href="client_orders.php">📦 Mes Commandes</a>
            <a href="client_history.php">📚 Historique</a>
            <a href="logout.php" class="logout">🚪 Déconnexion</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="search-bar">
            <form method="GET" action="client_dashboard.php">
                <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">🔍</button>
            </form>
        </header>

        <section class="product-grid">
            <?php if ($produits): ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                        <p><?= htmlspecialchars(substr($produit['description'], 0, 60)) ?>...</p>
                        <span class="price"><?= $produit['prix'] ?> €</span>
                        <a href="../product/details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun produit trouvé.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
