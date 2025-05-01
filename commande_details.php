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
$stmt = $pdo->prepare("CALL GetCommandeDetails(?)");
$stmt->execute([$id_commande]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:rgb(253, 248, 211);
            color: #3E2723;
        }

        h1 {
            text-align: center;
            margin-top: 30px;
            font-size: 32px;
            color: #4E342E;
        }

        .detail-container {
            padding: 40px;
            background-color: #FFFFFF;
            max-width: 1000px;
            margin: 40px auto;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #FFF8E1;
            border-radius: 12px;
            overflow: hidden;
        }

        .details-table th,
        .details-table td {
            padding: 14px;
            text-align: center;
            font-size: 16px;
        }

        .details-table th {
            background:  #F3E5AB;
            color: #3E2723;
            font-weight: bold;
        }

        .details-table td {
            background-color: #FFFDF7;
            border-bottom: 1px solid #E0D8CF;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .total-row td {
            font-weight: bold;
            background-color: #F5F5DC;
        }

        .back-button {
            display: inline-block;
            margin: 30px auto 0;
            padding: 12px 24px;
            background:  #8B5E33;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            text-align: center;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background:  #6D4C41;
        }

        @media (max-width: 768px) {
            .detail-container {
                padding: 20px;
                margin: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .details-table th,
            .details-table td {
                font-size: 14px;
                padding: 10px;
            }

            .back-button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="detail-container">
        <h1>Détails de la Commande #<?= htmlspecialchars($id_commande) ?></h1>

        <?php if (count($details) > 0): ?>
            <table class="details-table">
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
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;">Total de la commande :</td>
                        <td><?= number_format($total_commande, 2) ?> DA</td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun produit trouvé pour cette commande.</p>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="history.php" class="back-button">← Retour à l'historique</a>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
