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
        <a href="contact.php">Contact</a>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['user']) ?></span>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php">Créer un compte</a>
        <?php endif; ?>
    </nav>
</header>
