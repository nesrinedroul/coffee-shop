<?php
session_start();
$commande_id = $_GET['commande'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande confirmée</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>
    <div class="container">
        <h1>Merci pour votre commande !</h1>
        <?php if ($commande_id): ?>
            <p>Votre commande n° <strong><?php echo htmlspecialchars($commande_id); ?></strong> a bien été enregistrée.</p>
        <?php else: ?>
            <p>Votre commande a bien été enregistrée.</p>
        <?php endif; ?>
        <a href="index.php">Retour à l'accueil</a>
    </div>
</body>
</html>
