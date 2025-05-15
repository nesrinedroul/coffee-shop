<?php
session_start();
include('includes/db.php');

// Récupération des filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Récupération des catégories
$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Requête pour tous les produits (pour la recherche JS)
$allProducts = $pdo->query("SELECT id_produit, nom, prix, image, stock FROM produit")->fetchAll(PDO::FETCH_ASSOC);

// Requête filtrée (pour l'affichage principal)
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
    <link rel="stylesheet" href="assets/css/produit.css">
    <style>
        
    </style>
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
    
    <div class="products">
        <?php if ($produits): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <?php if ($produit['stock'] < 5): ?>
                            <span class="badge stock-warning">Stock limité</span>
                        <?php else: ?>
                            <span class="badge stock-ok">Disponible</span>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars($produit['description']) ?></p>
                    <span class="price"><?= number_format($produit['prix'], 2) ?> €</span>
                    <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir Détail</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Aucun produit trouvé pour votre recherche.</p>
        <?php endif; ?>
    </div>
</section>

<script>
// Stocke tous les produits en JavaScript
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
                        <strong>${product.name}</strong>
                        <div>${product.price.toFixed(2)} €</div>
                        <small>${product.stock < 5 ? 'Stock limité' : 'Disponible'}</small>
                    </div>
                </a>
            </div>`;
        });
        
        liveResults.innerHTML = html;
        liveResults.style.display = 'block';
    }
    
    // Cacher les résultats quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#search-input') && !e.target.closest('#live-results')) {
            liveResults.style.display = 'none';
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>