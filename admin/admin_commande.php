<?php
session_start();
require_once '../includes/db.php';


try {
    $stmt = $pdo->prepare("CALL ListerToutesLesCommandes()");
    $stmt->execute();
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts + CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #333;
            padding: 30px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .table-wrapper {
            overflow-x: auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            padding: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        thead {
            background-color: #111827;
            color: white;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.95rem;
        }

        th {
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f3f4f6;
        }

        tr:hover {
            background-color: #e5e7eb;
            transition: 0.2s;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #2563eb;
            color: white;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s ease;
        }

        .back-link:hover {
            background-color: #1e40af;
        }

        .empty-message {
            text-align: center;
            padding: 30px 0;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            th, td {
                font-size: 0.85rem;
                padding: 10px;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
 <?php include('admin_header.php'); ?>
    <div class=" main-content">
        <div class="header">
            <h2>Gestion des Commandes</h2>
        </div>
   

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>ID Client</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($commandes): ?>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?= htmlspecialchars($commande['id_commande']) ?></td>
                            <td><?= htmlspecialchars($commande['date_commande']) ?></td>
                            <td><?= number_format($commande['total'], 2) ?> DA</td>
                            <td><?= htmlspecialchars($commande['statut']) ?></td>
                            <td><?= htmlspecialchars($commande['id_utilisateur']) ?></td>
                            <td><?= htmlspecialchars($commande['nom']) ?></td>
                            <td><?= htmlspecialchars($commande['prenom']) ?></td>
                            <td><?= htmlspecialchars($commande['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-message">❗ Aucune commande trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="admin_dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>

</body>
</html>
