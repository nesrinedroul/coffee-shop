<?php
session_start();
include('includes/db.php');

// Récupération des filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Récupération des catégories pour la liste déroulante
$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Construction dynamique de la requête SQL
$query = "SELECT * FROM produit WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND nom LIKE ?";
    $params[] = "%$search%";
}
if (!empty($category)) {
    $query .= " AND categorie = ?";
    $params[] = $category;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Produits | Coffee Ness ☕</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<section class="search-section">
    <h2>Rechercher un produit</h2>
    <form method="get">
        <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>>
                    <?= ucfirst($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔍 Rechercher</button>
    </form>
</section>

<section id="products">
    <h2>Nos Produits <?= $category ? '(' . ucfirst($category) . ')' : '' ?></h2>
    <div class="products">
        <?php if ($produits): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars($produit['description']) ?></p>
                    <span class="price"><?= $produit['prix'] ?> €</span>
                    <a href="product/details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir Détail</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé pour votre recherche.</p>
        <?php endif; ?>
    </div>
</section>

<?php include('includes/footer.php'); ?>
</body>
</html>
