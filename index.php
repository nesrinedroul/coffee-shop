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
    <style>
        :root {
            --primary: #6f4e37;
            --secondary: #a67c52;
            --light: #f8f5f2;
            --dark: #2c1810;
            --success: #2ecc71;
            --error: #e74c3c;
        }

        /* Enhanced Hero Section */
        .hero {
            background: linear-gradient(rgba(44, 24, 16, 0.8), rgba(44, 24, 16, 0.8)),
                        url('assets/images/coffee-bg.jpg') center/cover;
            color: white;
            padding: 8rem 1rem 4rem;
            text-align: center;
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero h2 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .btn-hero {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(111, 78, 55, 0.4);
        }

        /* Modern Search Section */
        .search-section {
            background: var(--light);
            padding: 2rem 0;
        }

        .search-section form {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
            padding: 0 1rem;
        }

        .search-section input[type="text"],
        .search-section select {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid var(--primary);
            border-radius: 25px;
            font-size: 1rem;
        }

        .search-section button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        /* Product Cards */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-card .badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .product-card .price {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1rem 0;
        }

        .product-card .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        /* Products Grid */
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        /* Cookie Popup Modernization */
        .cookie-popup {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            padding: 1.5rem;
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .cookie-popup.visible {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h2 {
                font-size: 2.5rem;
            }

            .search-section form {
                flex-direction: column;
            }

            .products {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
    </style>
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