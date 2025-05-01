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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

</head>
<style>
    .cookie-popup {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #fff4e5;
    color: #4a2c1a;
    padding: 20px;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.2);
    z-index: 100;
  }
  
  .cookie-popup .cookie-content {
    max-width: 600px;
    margin: auto;
    text-align: center;
  }
  
  .cookie-popup button {
    margin: 10px;
    padding: 10px 15px;
    background-color: #6f4e37;
    color: white;
    border: none;
    cursor: pointer;
  }
  .hidden {
      display: none;
    }
  .cookie-popup.hidden {
    display: none;
  }
</style>
<script src="assets/js/cookies.js"></script>
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
    <div class="products-slider">
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
<!-- Cookie Consent Popup -->
<div id="cookie-consent-popup" class="cookie-popup">
  <div class="cookie-content">
    <h3>🍪 Nous utilisons des cookies</h3>
    <p>Nous utilisons des cookies pour améliorer votre expérience. Vous pouvez accepter tous les cookies ou gérer vos préférences.</p>
    <div class="cookie-buttons">
      <button id="accept-all-btn">Tout accepter</button>
      <button id="customize-btn">Personnaliser</button>
    </div>
  </div>
</div>

<!-- Preferences Panel -->
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
<!-- Swiper JS -->
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

</body>

</html>
