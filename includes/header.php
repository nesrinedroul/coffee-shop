<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <h1 class="logo">Coffee Ness</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="produit.php">Produits</a>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="cart.php">Mon Panier</a>
            <a href="history.php">historique</a>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php">Créer un compte</a>
        <?php endif; ?>
    </nav>
</header>
