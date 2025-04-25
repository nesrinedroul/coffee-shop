<?php
session_start();
include('includes/db.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

$stmtLatest = $pdo->query("SELECT * FROM produit ORDER BY id_produit desc LIMIT 4");
$latestProducts = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Coffee Ness ☕</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<!-- HERO SECTION -->
<section class="hero">
<?php if (isset($_SESSION['username'])): ?>
    
    <h2>☕ Bienvenue chez Coffee Bliss<span class="welcome">  <?= htmlspecialchars($_SESSION['username']) ?></span> </h2>
<?php else: ?>
    <h2>☕ Bienvenue chez Coffee Bliss</h2>
<?php endif; ?>
    <p>Un café qui réveille vos sens – fraîcheur, passion et arômes d’exception</p>
    <a href="#products" class="btn-hero">Voir nos cafés</a>
</section>
<section class="search-section">
    <form action="produit.php" method="get">
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
<section id="products">
    <h2>Nos Produits <?= $category ? '(' . ucfirst($category) . ')' : '' ?></h2>
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
            <p>Aucun produit trouvé pour votre recherche.</p>
        <?php endif; ?>
    </div>
</section>

<?php include('includes/footer.php'); ?>
<script>
  document.querySelector('.btn-hero').addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelector('#products').scrollIntoView({ behavior: 'smooth' });
  });
</script>


</body>
</html>
