<?php
session_start();
include('includes/db.php');

// Check if this is a live search request
$isLiveSearch = isset($_GET['live']) && $_GET['live'] == '1';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Get all categories for dropdown
$stmtCat = $pdo->query("SELECT DISTINCT categorie FROM produit");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

// Build search query
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

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle live search results (displayed in iframe)
if ($isLiveSearch) {
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background: white;
            }
            .live-result-item {
                padding: 10px;
                display: flex;
                align-items: center;
                border-bottom: 1px solid #eee;
                cursor: pointer;
                text-decoration: none;
                color: #333;
            }
            .live-result-item:hover {
                background-color: #f5f5f5;
            }
            .live-result-item img {
                width: 40px;
                height: 40px;
                object-fit: cover;
                margin-right: 10px;
                border-radius: 3px;
            }
            .live-result-info {
                flex-grow: 1;
            }
            .live-result-info h4 {
                margin: 0;
                font-size: 14px;
                color: #333;
            }
            .live-result-info p {
                margin: 2px 0 0;
                font-size: 12px;
                color: #666;
            }
            .no-results {
                padding: 10px;
                color: #666;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php if (!empty($produits)): ?>
            <?php foreach ($produits as $produit): ?>
                <a href="details.php?id=<?= $produit['id_produit'] ?>" class="live-result-item" target="_parent">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                    <div class="live-result-info">
                        <h4><?= htmlspecialchars($produit['nom']) ?></h4>
                        <p><?= number_format($produit['prix'], 2) ?> €</p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">Aucun résultat trouvé pour "<?= htmlspecialchars($search) ?>"</div>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de recherche | Coffee Ness ☕</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .search-container {
            position: relative;
            max-width: 800px;
            margin: 20px auto;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-form select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-form button {
            padding: 10px 20px;
            background: #6f4e37;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
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
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
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
        }
        
        iframe#live-search-results {
            width: 100%;
            height: 300px;
            border: none;
        }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="search-container">
    <form class="search-form" method="get" action="search.php">
        <input type="text" name="search" placeholder="Rechercher un produit..." 
               value="<?= htmlspecialchars($search) ?>" autocomplete="off" id="search-input">
        
        <select name="category">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" 
                    <?= $cat === $category ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($cat)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">🔍 Rechercher</button>
    </form>
    
    <div id="live-results-container" style="display: none;">
        <iframe id="live-search-results" src="about:blank" name="live-search-frame"></iframe>
    </div>
</div>

<section class="search-results">
    <h2><?= empty($search) ? 'Tous nos produits' : 'Résultats pour "'.htmlspecialchars($search).'"' ?></h2>
    
    <?php if (!empty($produits)): ?>
        <div class="products">
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" 
                         alt="<?= htmlspecialchars($produit['nom']) ?>" 
                         class="product-image">
                    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                    <p><?= htmlspecialchars($produit['description']) ?></p>
                    <p class="price"><?= number_format($produit['prix'], 2) ?> €</p>
                    <a href="details.php?id=<?= $produit['id_produit'] ?>" class="btn">Voir détails</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <p>Aucun produit trouvé pour votre recherche.</p>
            <?php if (!empty($search)): ?>
                <p>Essayez avec des termes différents ou moins spécifiques.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php include('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const liveResultsContainer = document.getElementById('live-results-container');
    const liveResultsIframe = document.getElementById('live-search-results');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        if (searchTerm.length > 0) {
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
    
    // Allow form submission to filter products on this page
    document.querySelector('.search-form').addEventListener('submit', function(e) {
        // This will refresh the search page with the new filters
    });
});
</script>
</body>
</html>