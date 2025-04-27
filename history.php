<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$statut = $_GET['statut'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

$stmt = $pdo->prepare("CALL HistoriqueClientFiltre(?, ?, ?, ?)");
$stmt->execute([$user_id, $statut ?: null, $date_debut ?: null, $date_fin ?: null]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Commandes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff7f0;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            padding: 40px;
            background-color: #fefefe;
        }

        h1 {
            font-size: 28px;
            color: #5c3d2e;
            margin-bottom: 30px;
            text-align: center;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        form label {
            font-weight: bold;
            color: #333;
        }

        form select,
        form input[type="date"],
        form button {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background-color: #8b4513;
            color: white;
            border: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f3e8dd;
            color: #5c3d2e;
        }

        td form button,
        td button {
            background-color: #b22222;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        #cancelModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        #cancelModal .modal-content {
            background-color: #fff7f0;
            padding: 30px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }

        #cancelModal .modal-content button {
            margin: 10px 5px 0;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #cancelModal .modal-content .confirm {
            background-color: #b22222;
            color: white;
        }

        #cancelModal .modal-content .cancel {
            background-color: #888;
            color: white;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Historique de vos Commandes</h1>

        <form method="GET">
            <label for="statut">Statut :</label>
            <select name="statut" id="statut">
                <option value="">Tous</option>
                <option value="en_attente" <?= $statut == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="validee" <?= $statut == 'validee' ? 'selected' : '' ?>>Validée</option>
                <option value="annulee" <?= $statut == 'annulee' ? 'selected' : '' ?>>Annulée</option>
            </select>

            <label for="date_debut">Date de début :</label>
            <input type="date" name="date_debut" value="<?= $date_debut ?>">

            <label for="date_fin">Date de fin :</label>
            <input type="date" name="date_fin" value="<?= $date_fin ?>">

            <button type="submit">Filtrer</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Total</th>
                    <th>Détails</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $commande): ?>
                    <tr>
                        <td><?= htmlspecialchars($commande['date_commande']) ?></td>
                        <td><?= htmlspecialchars($commande['statut']) ?></td>
                        <td><?= number_format($commande['total'], 2) ?> DA</td>
                        <td>
                            <form method="GET" action="commande_details.php">
                                <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                <button type="submit">Voir</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($commande['statut'] === 'en_attente'): ?>
                                <button type="button" onclick="openModal(<?= $commande['id_commande'] ?>)">Annuler</button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="cancelModal">
        <div class="modal-content">
            <p>Voulez-vous vraiment annuler cette commande ?</p>
            <form method="POST" action="" id="cancelForm">
                <input type="hidden" name="id_commande" id="modalCommandeId">
                <button type="submit" class="confirm">Oui, annuler</button>
                <button type="button" onclick="closeModal()" class="cancel">Annuler</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById('modalCommandeId').value = id;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>
