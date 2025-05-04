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
    <title>Coffee Bliss ☕ - Boutique en ligne</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <meta charset="UTF-8">
    <title>Coffee Ness ☕</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
</head>
<body>

<?php include('includes/header.php'); ?>

<!-- Enhanced Hero Section -->
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

<!-- Modern Search Section -->
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
                            <span class="price"><?= number_format($produit['prix'], 2) ?> €</span>
                            <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir le produit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

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
                                <span class="price"><?= number_format($produit['prix'], 2) ?> €</span>
                                <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Détails</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-results">Aucun produit trouvé pour votre recherche.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
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
            <label>
                <input type="checkbox" checked disabled> Cookies essentiels (obligatoire)
            </label><br>
            <label>
                <input type="checkbox" name="analytics" id="analytics"> Cookies de performance (ex: Google Analytics)
            </label><br>
            <label>
                <input type="checkbox" name="marketing" id="marketing"> Cookies marketing (ex: Facebook Pixel)
            </label><br><br>
            <button type="submit">Sauvegarder mes choix</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
$(document).ready(function(){
  $('.products-slider').slick({
    autoplay: true,
    autoplaySpeed: 3000,
    dots: true,
    arrows: false,
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
        {
          breakpoint: 768,
          settings: {
          slidesToShow: 1
          }
        }
    ]
  });
});
</script>
<script src="assets/js/cookies.js"></script>
</body>
</html>