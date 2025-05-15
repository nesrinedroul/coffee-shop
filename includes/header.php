<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
  *{
     font-family: 'Playfair Display', serif;
  }
    header {
       font-family: 'Playfair Display', serif;
    background: #451f04;
    color: #fff;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
 
}
  
  header h1 {
    font-size: 1.8rem;
    color: #ffe5b4;
  }
  
  header nav a {
    color: #fff;
    margin-left: 1rem;
    text-decoration: none;
  }
</style>
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
