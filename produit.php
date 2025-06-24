<?php
session_start();
include('includes/db.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

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

$allProducts = $pdo->query("SELECT id_produit, nom, prix, image, stock FROM produit")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Coffee Ness ☕</title>
    <link rel="stylesheet" href="assets/css/produit.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">

</head>
<body>

<?php include('includes/header.php'); ?>

<section class="search-section">
    <form action="produit.php" method="get" id="search-form">
        <div class="search-container">
            <input type="text" id="search-input" name="search" 
                   placeholder="Rechercher un produit..." 
                   value="<?= htmlspecialchars($search) ?>"
                   autocomplete="off">
            <div id="live-results"></div>
        </div>
        
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

<section id="products">
    <h2>Nos Produits <?= $category ? '(' . ucfirst(htmlspecialchars($category)) . ')' : '' ?></h2>
    
    <div class="products ">
        <?php if ($produits): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <?php if ($produit['stock'] < 5): ?>
                            <span class="badge stock-warning">Stock limité</span>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars(substr($produit['description'], 0, 100)) ?>...</p>
                    <div class="product-footer">
                        <span class="price"><?= number_format($produit['prix'], 2) ?> DZD</span>
                        <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Détails</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Aucun produit trouvé pour votre recherche.</p>
        <?php endif; ?>
    </div>
</section>

<script>

const allProducts = [
    <?php 
    foreach ($allProducts as $p) {
        echo "{
            id: ".$p['id_produit'].",
            name: '".addslashes($p['nom'])."',
            price: ".$p['prix'].",
            image: '".$p['image']."',
            stock: ".$p['stock']."
        },";
    }
    ?>
];

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const liveResults = document.getElementById('live-results');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        if (searchTerm.length < 2) {
            liveResults.style.display = 'none';
            liveResults.innerHTML = '';
            return;
        }
        
        const filteredProducts = allProducts.filter(product => 
            product.name.toLowerCase().includes(searchTerm)
        ).slice(0, 5); // Limite à 5 résultats
        
        displayLiveResults(filteredProducts);
    });
    
    function displayLiveResults(products) {
        if (products.length === 0) {
            liveResults.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
            liveResults.style.display = 'block';
            return;
        }
        
        let html = '';
        products.forEach(product => {
            html += `
            <div class="live-result-item">
                <a href="details.php?id=${product.id}">
                    <img src="${product.image}" alt="${product.name}">
                    <div>
                        <h4>${product.name}</h4>
                        <p>${product.price.toFixed(2)} DZD</p>
                        <small>${product.stock < 5 ? 'Stock limité' : 'Disponible'}</small>
                    </div>
                </a>
            </div>`;
        });
        
        liveResults.innerHTML = html;
        liveResults.style.display = 'block';
    }
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !liveResults.contains(e.target)) {
            liveResults.style.display = 'none';
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>