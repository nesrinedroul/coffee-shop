<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';

try {

    $sql = "SELECT * FROM commande WHERE id_utilisateur = ?";
    $params = [$user_id];

    if (!empty($statut)) {
        $sql .= " AND statut = ?";
        $params[] = $statut;
    }
    
    if (!empty($date_debut)) {
        $sql .= " AND date_commande >= ?";
        $params[] = $date_debut . ' 00:00:00';
    }
    
    if (!empty($date_fin)) {
        $sql .= " AND date_commande <= ?";
        $params[] = $date_fin . ' 23:59:59';
    }
    
    $sql .= " ORDER BY date_commande DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des commandes : " . $e->getMessage();
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_commande'])) {
    $commande_id = $_POST['commande_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_utilisateur = ? AND statut = 'en_attente'");
        $stmt->execute([$commande_id, $user_id]);
        $commande = $stmt->fetch();

        if ($commande) {
            $stmt = $pdo->prepare("UPDATE commande SET statut = 'annulee' WHERE id_commande = ?");
            $stmt->execute([$commande_id]);
            
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/history.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1><i class="fas fa-history"></i> Historique des Commandes</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="GET" class="filter-form">
            <div>
                <label for="statut">Statut</label>
                <select name="statut" id="statut">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?= $statut == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="validee" <?= $statut == 'validee' ? 'selected' : '' ?>>Validée</option>
                    <option value="annulee" <?= $statut == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>
            
            <div>
                <label for="date_debut">Date de début</label>
                <input type="date" name="date_debut" id="date_debut" value="<?= $date_debut ?>">
            </div>
            
            <div>
                <label for="date_fin">Date de fin</label>
                <input type="date" name="date_fin" id="date_fin" value="<?= $date_fin ?>">
            </div>
            
            <div>
                <button type="submit">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </div>
        </form>
        
        <!-- Tableau des commandes -->
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commandes)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Aucune commande trouvée</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td>#<?= str_pad($commande['id_commande'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></td>
                            <td>
                                <span class="statut-badge statut-<?= $commande['statut'] ?>">
                                    <?php if ($commande['statut'] == 'en_attente'): ?>
                                        <i class="fas fa-clock"></i>
                                    <?php elseif ($commande['statut'] == 'validee'): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times"></i>
                                    <?php endif; ?>
                                    <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                                </span>
                            </td>
                            <td><?= number_format($commande['total'], 2, ',', ' ') ?> DA</td>
                            <td>
                                <div class="btn-group">
                                    <form action="commande_details.php" method="get" style="display: inline;">
                                        <button class="btn btn-primary" type="submit" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                            <i class="fas fa-eye"></i> Détails
                                        </button>
                                    </form>
                                    <?php if ($commande['statut'] === 'en_attente'): ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-danger"
                                            onclick="openModal(
                                                <?= $commande['id_commande'] ?>, 
                                                '<?= htmlspecialchars($commande['date_commande'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-ban"></i> Annuler
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal d'annulation -->
    <div id="cancelModal">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Annulation de commande</h3>
            <p>Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.</p>
            <form method="POST" action="" id="cancelForm">
                <input type="hidden" name="commande_id" id="modalCommandeId">
                <input type="hidden" name="annuler_commande" value="1">
                <div class="modal-buttons">
                    <button type="button" onclick="closeModal()" class="cancel">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="confirm">
                        <i class="fas fa-check"></i> Confirmer
                    </button>
                </div>
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
                alert("Désolé, vous ne pouvez plus annuler cette commande après 24 heures !");
                return;
            }

            document.getElementById('modalCommandeId').value = id;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('cancelModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
    <?php include('includes/footer.php'); ?>
</body>
</html>