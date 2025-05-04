<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Paramètres de filtrage
$mot_cle = !empty($_GET['mot_cle']) ? $_GET['mot_cle'] : null;
$statut = !empty($_GET['statut']) ? $_GET['statut'] : null;
$date_debut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$date_fin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;
$page = max(1, $_GET['page'] ?? 1);
$per_page = 20;

// Liste des statuts possibles
$statuts_disponibles = ['en_attente', 'validee', 'annulee'];

try {
    $stmt = $pdo->prepare("CALL ListerCommandesAdminFiltre(?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mot_cle, $statut, $date_debut, $date_fin, $page, $per_page]);

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// Fonction pour afficher un statut lisible
function formatStatut($statut) {
    $statuts = [
        'en_attente' => '⏳ En attente',
        'validee' => '✅ Validée', 
        'annulee' => '❌ Annulée'
    ];
    return $statuts[$statut] ?? $statut;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
        }.header-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .sortable {
            cursor: pointer;
            transition: 0.3s;
        }

        .sortable:hover {
            background: #f3f4f6;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .pagination a {
            padding: 8px 12px;
            border-radius: 6px;
            background: #f3f4f6;
            text-decoration: none;
        }

        .pagination a.active {
            background: #3b82f6;
            color: white;
        }

        .export-btn {
            background: #10b981;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .statut-selector {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
        }

    </style>
</head>
<body>
<?php include('admin_header.php'); ?>

<div class="main-content">
    <div class="header-tools">
        <h1>Gestion des commandes</h1>
        <a href="export.php?<?= http_build_query($_GET) ?>" class="export-btn">
            <i class='bx bx-download'></i> Exporter CSV
        </a>
    </div>

    <form method="GET" class="filters">
        <div class="filter-grid">
            <!-- À compléter avec des champs input/select si souhaité -->
            <input type="text" name="mot_cle" placeholder="Recherche..." value="<?= htmlspecialchars($_GET['mot_cle'] ?? '') ?>">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <?php foreach ($statuts_disponibles as $s): ?>
                    <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>>
                        <?= formatStatut($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut ?? '') ?>">
            <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin ?? '') ?>">
        </div>
        <button type="submit" class="btn-filter">Appliquer les filtres</button>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($commandes)): ?>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?= $commande['id_commande'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                            <td><?= htmlspecialchars($commande['prenom']) . ' ' . htmlspecialchars($commande['nom']) ?></td>
                            <td><?= number_format($commande['total'], 2) ?> DA</td>
                            <td>
                                <select class="statut-selector"
                                        data-order="<?= $commande['id_commande'] ?>"
                                        data-current="<?= $commande['statut'] ?>">
                                    <?php foreach ($statuts_disponibles as $s): ?>
                                        <option value="<?= $s ?>" <?= $s === $commande['statut'] ? 'selected' : '' ?>>
                                            <?= formatStatut($s) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <a href="../details.php?id=<?= $commande['id_commande'] ?>" class="back-link">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-message">Aucune commande trouvée pour les critères sélectionnés.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination simplifiée -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $page + 1; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
               class="<?= $i == $page ? 'active' : '' ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<script>
   <script>
document.querySelectorAll('.statut-selector').forEach(selector => {
    selector.addEventListener('change', function () {
        const orderId = this.dataset.order;
        const currentStatus = this.dataset.current;
        const newStatus = this.value;
        const selectElement = this;

        if (newStatus === currentStatus) return;

        Swal.fire({
            title: "Modifier le statut ?",
            text: `Commande #${orderId} - Nouveau statut : ${newStatus}`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Confirmer",
            cancelButtonText: "Annuler"
        }).then(result => {
            if (result.isConfirmed) {
                fetch('update_statu.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_id: orderId,
                        new_status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("Succès", "Le statut a été mis à jour.", "success");
                        selectElement.dataset.current = newStatus; // Mettre à jour la valeur actuelle
                    } else {
                        Swal.fire("Erreur", data.message || "Échec de la mise à jour.", "error");
                        selectElement.value = currentStatus;
                    }
                })
                .catch(() => {
                    Swal.fire("Erreur", "Une erreur est survenue.", "error");
                    selectElement.value = currentStatus;
                });
            } else {
                selectElement.value = currentStatus;
            }
        });
    });
});
</script>

</script>
</body>
</html>
