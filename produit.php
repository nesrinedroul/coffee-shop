<?php
// produit.php
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
    <style>
        .search-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        #search-form {
            display: flex;
        }
        
        #search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        
        #search-button {
            padding: 10px 15px;
            background: #6f4e37;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        #live-results-container {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            display: none;
        }
        
        iframe#live-search-results {
            width: 100%;
            height: 300px;
            border: none;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.2s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .product-card .price {
            font-weight: bold;
            color: #6f4e37;
            font-size: 1.1em;
            margin: 10px 0;
            display: block;
        }
        
        .product-card .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #6f4e37;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #6f4e37;
        }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="search-container">
    <form id="search-form" action="produit.php" method="get">
        <input type="text" id="search-input" name="search" placeholder="Rechercher un produit..." 
               value="<?= htmlspecialchars($search) ?>" autocomplete="off">
        <button type="submit" id="search-button">🔍</button>
    </form>
    
    <div id="live-results-container">
        <iframe id="live-search-results" src="about:blank" name="live-search-frame"></iframe>
    </div>
</div>

<section id="products">
    <h2>Nos Produits <?= $category ? '(' . ucfirst(htmlspecialchars($category)) . ')' : '' ?></h2>
    
    <div class="products">
        <?php if ($produits): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars($produit['description']) ?></p>
                    <span class="price"><?= number_format($produit['prix'], 2) ?> €</span>
                    <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir Détail</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé pour votre recherche.</p>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const liveResultsContainer = document.getElementById('live-results-container');
    const liveResultsIframe = document.getElementById('live-search-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        if (searchTerm.length > 0) {
            // Load search results in the iframe
            liveResultsIframe.src = `search.php?live=1&search=${encodeURIComponent(searchTerm)}`;
            liveResultsContainer.style.display = 'block';
        } else {
            liveResultsContainer.style.display = 'none';
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !liveResultsContainer.contains(e.target)) {
            liveResultsContainer.style.display = 'none';
        }
    });

    // Form submission to filter products on the current page
    document.getElementById('search-form').addEventListener('submit', function(e) {
        // The form now submits to produit.php, so it will filter products on this page
    });
});
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>