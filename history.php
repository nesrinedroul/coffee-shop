<?php
session_start();
require 'includes/db.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'id de l'utilisateur
$user_id = $_SESSION['user_id'];

// Récupérer l'historique des commandes
try {
    // Préparer la requête pour récupérer les commandes de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);

    // Vérifier si des commandes ont été récupérées
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des commandes : " . $e->getMessage();
    header("Location: index.php");
    exit();
}

// Si une erreur survient lors de l'annulation de la commande
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}

// Si une commande a été annulée avec succès
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}

// Traitement de l'annulation de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_commande'])) {
    $commande_id = $_POST['commande_id'];
    $raison = $_POST['raison'];

    try {
        // Vérifier si la commande existe et si elle est en attente
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_utilisateur = ? AND statut = 'en_attente'");
        $stmt->execute([$commande_id, $user_id]);
        $commande = $stmt->fetch();

        if ($commande) {
            // Mise à jour du statut de la commande à "annulée"
            $stmt = $pdo->prepare("UPDATE commande SET statut = 'annulee' WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Enregistrement dans l'historique des annulations
            $stmt = $pdo->prepare("INSERT INTO historique_commandes_annulees (id_commande, date_annulation, raison) VALUES (?, NOW(), ?)");
            $stmt->execute([$commande_id, $raison]);

            $_SESSION['success'] = "La commande a été annulée avec succès.";
        } else {
            $_SESSION['error'] = "Cette commande ne peut pas être annulée.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de l'annulation de la commande : " . $e->getMessage();
    }

    // Redirection après l'annulation
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
        .modal { /* Styles pour le modal */ }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Historique des Commandes</h1>

        <!-- Formulaire de filtrage -->
        <form method="GET" class="filter-form">
            <!-- ... garder les mêmes champs de filtre ... -->
        </form>

        <!-- Tableau des commandes -->
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
                                    onclick="openCancelModal(<?= $commande['id_commande'] ?>)"
                                >
                                    Annuler
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   <!-- Modal d'annulation -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <h3>Annuler une commande</h3>
        <form method="POST">
            <input type="hidden" name="commande_id" id="commandeId">
            <div class="form-group">
                <label for="raison">Raison de l'annulation :</label>
                <textarea 
                    name="raison" 
                    id="raison" 
                    class="form-control"
                    rows="3"
                    required
                ></textarea>
            </div>
            <div class="modal-actions">
                <button type="submit" name="annuler_commande" class="btn btn-danger">
                    Confirmer l'annulation
                </button>
                <button 
                    type="button" 
                    class="btn btn-secondary"
                    onclick="closeCancelModal()"
                >
                    Fermer
                </button>
            </div>
        </form>
    </div>
</div>


    <script>
        function openCancelModal(commandeId) {
            document.getElementById('commandeId').value = commandeId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    </script>
    <?php include('includes/footer.php'); ?>
</body>
</html>