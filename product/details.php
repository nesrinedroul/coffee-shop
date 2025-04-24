<?php
session_start();
include('../includes/db.php');

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produit non trouvé.";
    exit;
}

$id = (int) $_GET['id'];

// Récupérer les détails du produit
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "Produit introuvable.";
    exit;
}

// Traitement du formulaire d'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produit'])) {
    if (!isset($_SESSION['user'])) {
        // Rediriger vers la page de login si l'utilisateur n'est pas connecté
        header("Location: ../login.php");
        exit;
    }

    $productId = (int) $_POST['id_produit'];
    $quantity = (int) $_POST['quantite'];

    // Ajouter ou mettre à jour le produit dans le panier
    if (isset($_SESSION['cart'][$productId])) {
        // Si le produit est déjà dans le panier, on ajoute la quantité demandée
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        // Sinon, on l'ajoute au panier avec la quantité demandée
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity
        ];
    }

    // Rediriger l'utilisateur vers la page du panier
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produit['nom']) ?> - Coffee Bliss</title>
    <link rel="stylesheet" href="../assets/css/details.css">
</head>
<body>

<header>
    <h1 class="logo">Coffee Bliss</h1>
    <nav>
        <a href="../user/client_dashboard.php">Accueil</a>
        <a href="../product/cart.php">🛒 Panier</a>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['user']) ?></span>
            <a href="../logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="../login.php">Connexion</a>
            <a href="../register.php">Créer un compte</a>
        <?php endif; ?>
    </nav>
</header>

<!-- SECTION DÉTAILS DU PRODUIT -->
<main class="product-detail-container">
    <div class="product-image">
        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
    </div>
    <div class="product-info">
        <h2><?= htmlspecialchars($produit['nom']) ?></h2>
        <p class="description"><?= htmlspecialchars($produit['description']) ?></p>
        <p class="price"><strong>Prix :</strong> <?= $produit['prix'] ?> €</p>

        <!-- Si l'utilisateur est connecté, afficher le formulaire de commande -->
        <?php if ($_SESSION['user'] = $username): ?>
            <form method="post" action="details.php?id=<?= $produit['id_produit'] ?>">
                <label for="quantite">Quantité :</label>
                <input type="number" name="quantite" id="quantite" value="1" min="1" required>
                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                <button type="submit" class="btn-commander">Commander</button>
            </form>
        <?php else: ?>
            <!-- Sinon, afficher un lien vers la page de connexion -->
            <p class="warning-message">Veuillez <a href="../login.php">vous connecter</a> pour commander ce produit.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

