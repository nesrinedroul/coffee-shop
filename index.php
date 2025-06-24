<?php
session_start();
include('includes/db.php');

// Récupération des catégories
$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Récupération des produits les plus récents
$stmtLatest = $pdo->query("SELECT * FROM produit ORDER BY id_produit DESC LIMIT 6");
$latestProducts = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

// Récupération des images associées au premier produit par catégorie
$categoryImageMap = [];

$sql = "SELECT p1.categorie, p1.image
        FROM produit p1
        JOIN (
            SELECT categorie, MIN(id_produit) AS min_id
            FROM produit
            GROUP BY categorie
        ) p2 ON p1.categorie = p2.categorie AND p1.id_produit = p2.min_id";

$stmtImages = $pdo->query($sql);
$results = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $categoryImageMap[$row['categorie']] = $row['image'];
}

// Recherche et filtrage
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
</head>
<body>
<?php include('includes/header.php'); ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <?php if (isset($_SESSION['username'])): ?>
            <h2>Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> ☕</h2>
        <?php else: ?>
            <h2>L'artisanat du café d'exception</h2>
        <?php endif; ?>
        <p>Découvrez nos sélections premium de cafés torréfiés avec passion</p>
        <a href="#products" class="btn-hero">Explorer la collection</a>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <form action="produit.php" method="get">
        <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>>
                    <?= ucfirst($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔍 Rechercher</button>
    </form>
</section>

<!-- Catégories -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Nos Catégories</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): 
                $imageUrl = $categoryImageMap[$cat] ?? 'assets/img/default-category.jpg';
            ?>
                <a href="produit.php?category=<?= urlencode($cat) ?>" class="category-card">
                    <div class="category-image">
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($cat) ?>" loading="lazy">
                    </div>
                    <div class="category-info">
                        <h3><?= ucfirst(htmlspecialchars($cat)) ?></h3>
                        <span class="btn">Voir les produits</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Nouveautés -->
<section class="latest-products">
    <div class="container">
        <h2 class="section-title">Nouveautés</h2>
        <div class="products-slider">
            <?php foreach ($latestProducts as $produit): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <span class="badge new">Nouveau</span>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                        <p><?= htmlspecialchars(substr($produit['description'], 0, 80)) ?>...</p>
                        <div class="product-footer">
                            <span class="price"><?= number_format($produit['prix'], 2) ?> DZD</span>
                            <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produits -->
<section id="products" class="products-section">
    <div class="container">
        <h2 class="section-title">Notre collection <?= $category ? '(' . ucfirst($category) . ')' : '' ?></h2>
        <div class="products">
            <?php if ($produits): ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                            <?php if ($produit['stock'] < 5): ?>
                                <span class="badge stock-warning">Stock limité</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                            <p><?= htmlspecialchars(substr($produit['description'], 0, 100)) ?>...</p>
                            <div class="product-footer">
                                <span class="price"><?= number_format($produit['prix'], 2) ?> DZD</span>
                                <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Détails</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center;">Aucun produit trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Cookie Consent -->
<div id="cookie-consent-popup" class="cookie-popup hidden">
    <div class="cookie-content">
        <h3>🍪 Nous utilisons des cookies</h3>
        <p>Nous utilisons des cookies pour améliorer votre expérience. Vous pouvez accepter tous les cookies ou gérer vos préférences.</p>
        <div class="cookie-buttons">
            <button id="accept-all-btn">Tout accepter</button>
            <button id="customize-btn">Personnaliser</button>
        </div>
    </div>
</div>
<div id="cookie-preferences" class="cookie-popup hidden">
    <div class="cookie-content">
        <h4>Préférences des cookies</h4>
        <form id="cookie-form">
            <label><input type="checkbox" checked disabled> Cookies essentiels (obligatoire)</label><br>
            <label><input type="checkbox" name="analytics" id="analytics"> Cookies de performance</label><br>
            <label><input type="checkbox" name="marketing" id="marketing"> Cookies marketing</label><br><br>
            <button type="submit">Sauvegarder mes choix</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
$(document).ready(function(){
  $('.products-slider').slick({
    autoplay: true,
    autoplaySpeed: 2000,
    dots: true,
    arrows: false,
    infinite: true,
    slidesToShow: 4,
    slidesToScroll: 1,
    responsive: [
      { breakpoint: 992, settings: { slidesToShow: 2 } },
      { breakpoint: 600, settings: { slidesToShow: 1 } }
    ]
  });
});
</script>
<script src="assets/js/cookies.js"></script>
</body>
</html>
