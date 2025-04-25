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

// Call stored procedure
$stmt = $pdo->prepare("CALL GetCommandeDetails(?)");
$stmt->execute([$id_commande]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optional: fetch total (from the 2nd SELECT in the SP)
$stmt->nextRowset();
$totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
$total_commande = $totalRow['total_global'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la commande</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
.detail-container {
    padding: 40px;
    background-color: #fefefe;
    max-width: 1000px;
    margin: auto;
}

h2 {
    font-size: 24px;
    color: #5c3d2e;
    margin-bottom: 20px;
    text-align: center;
}

.commande-info {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #fff7f0;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

.commande-info p {
    margin: 10px 0;
    font-size: 16px;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.details-table th,
.details-table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

.details-table th {
    background-color: #f3e8dd;
    color: #5c3d2e;
}

.back-button {
    display: block;
    margin: 30px auto 0;
    background-color: #8b4513;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
}
</style>
<body>
    <?php include('includes/header.php'); ?>
    <h1>Détails de la Commande #<?= htmlspecialchars($id_commande) ?></h1>
    
    <?php if (count($details) > 0): ?>
        <table border="1" cellpadding="10">
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
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total de la commande :</strong></td>
                    <td><strong><?= number_format($total_commande, 2) ?> DA</strong></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun produit trouvé pour cette commande.</p>
    <?php endif; ?>

    <br>
    <a href="history.php">← Retour à l'historique</a>
    <?php include('includes/footer.php'); ?>
</body>
</html>
