<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

try {

    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des commandes : " . $e->getMessage();
    header("Location: index.php");
    exit();
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_commande'])) {
    $commande_id = $_POST['commande_id'];
    $raison = $_POST['raison'];

    try {
        // Vérifier que la commande est bien annulable
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_utilisateur = ? AND statut = 'en_attente'");
        $stmt->execute([$commande_id, $user_id]);
        $commande = $stmt->fetch();

        if ($commande) {
            // Mettre à jour le statut dans la table commande
            $stmt = $pdo->prepare("UPDATE commande SET statut = 'annulee' WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Historique d'annulation
            $stmt = $pdo->prepare("INSERT INTO historique_annulations (id_commande, raison, date_annulation) VALUES (?, ?, NOW())");
            $stmt->execute([$commande_id, $raison]);

            $_SESSION['success'] = "Commande annulée avec succès.";
        } else {
            $_SESSION['error'] = "Impossible d'annuler cette commande.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header("Location: history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Commandes</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .statut-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
        }
        .statut-en_attente { background: #fff3cd; color: #856404; }
        .statut-validee { background: #d4edda; color: #155724; }
        .statut-annulee { background: #f8d7da; color: #721c24; }
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
        <h1>Historique des Commandes</h1>
        <form method="GET" class="filter-form">
           
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
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td>#<?= $commande['id_commande'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                        <td>
                            <span class="statut-badge statut-<?= $commande['statut'] ?>">
                                <?= ucfirst($commande['statut']) ?>
                            </span>
                            <?php if ($commande['statut'] === 'annulee'): ?>
                                <br><small>Raison : <?= $commande['raison'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($commande['total'], 2) ?> DA</td>
                        <td>
                            <form action='commande_details.php' methode='get'>
                             <button class="btn" type="submit" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                Détails
                            </button>
                            </form>
    <?php if ($commande['statut'] === 'en_attente'): ?>
        <button 
            type="button" 
            class="btn btn-danger"
            onclick="openModal(
                <?= $commande['id_commande'] ?>, 
                '<?= htmlspecialchars($commande['date_commande'], ENT_QUOTES) ?>')">
            Annuler
        </button>
    <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   <!-- Modal d'annulation -->
   <div id="cancelModal">
        <div class="modal-content">
            <p>Voulez-vous vraiment annuler cette commande ?</p>
            <form method="POST" action="annuler_commande.php" id="cancelForm">
                <input type="hidden" name="id_commande" id="modalCommandeId">
                <button type="submit" class="confirm">Oui, annuler</button>
                <button type="button" onclick="closeModal()" class="cancel">Annuler</button>
            </form>
        </div>
    </div>

    <script>
      function openModal(id, date) {
    const MAX_HOURS = 24; // Délai maximum d'annulation en heures
    const commandeDate = new Date(date);
    const now = new Date();
    const diffHours = Math.abs(now - commandeDate) / 36e5;

    if (diffHours > MAX_HOURS) {
        alert("Annulation impossible après 24 heures !");
        return;
    }

    document.getElementById('modalCommandeId').value = id;
    document.getElementById('cancelModal').style.display = 'flex';
}
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>