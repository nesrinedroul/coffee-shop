<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_commande'])) {
    echo "Commande introuvable.";
    exit();
}

$id_commande = (int) $_GET['id_commande'];

try {
    // Debug: Check the value of id_commande
    var_dump($id_commande); // This will output the value to make sure it's correct
    exit();

    // Fetch details for the order using the stored procedure
    $stmt = $pdo->prepare("CALL GetCommandeDetails(?)");
    $stmt->execute([$id_commande]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->nextRowset();
    $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_commande = $totalRow['total_global'] ?? 0;

    if (!$details) {
        echo "Aucune commande trouvée pour cet ID.";
        exit();
    }

} catch (PDOException $e) {
    // If there is a database error, display it
    echo "Erreur lors de l'exécution de la requête: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la commande</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Détails de la Commande</h1>

    <!-- Check if details are available -->
    <?php if (count($details) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['produit']) ?></td>
                        <td><?= htmlspecialchars($item['quantite']) ?></td>
                        <td><?= number_format($item['prix_unitaire'], 2) ?> DA</td>
                        <td><?= number_format($item['total_par_produit'], 2) ?> DA</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune commande trouvée pour cet ID.</p>
    <?php endif; ?>
</body>
</html>
