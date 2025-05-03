<?php
session_start();
require_once '../includes/db.php';

// Vérification admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Conversion des paramètres vides en NULL
$mot_cle = !empty($_GET['mot_cle']) ? $_GET['mot_cle'] : null;
$statut = !empty($_GET['statut']) ? $_GET['statut'] : null;
$date_debut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$date_fin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;
$page = max(1, $_GET['page'] ?? 1);
$per_page = 20;

try {
    // Appel de la procédure
    $stmt = $pdo->prepare("CALL ListerCommandesAdminFiltre(?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mot_cle, $statut, $date_debut, $date_fin, $page, $per_page]);
    
    // Debugging
    if ($stmt->rowCount() === 0) {
        echo "Aucune commande trouvée. Paramètres utilisés :";
        echo "<pre>";
        print_r([$mot_cle, $statut, $date_debut, $date_fin]);
        echo "</pre>";
    }
    
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}



// Fonction de formatage
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
            </div>
            <button type="submit" class="btn-filter">Appliquer les filtres</button>
        </form>

        <!-- Tableau -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="sortable" data-sort="id">ID</th>
                        <th class="sortable" data-sort="date">Date</th>
                        <th>Client</th>
                        <th class="sortable" data-sort="total">Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?= $commande['id_commande'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                            <td>
                                <?= htmlspecialchars($commande['prenom']) ?> 
                                <?= htmlspecialchars($commande['nom']) ?>
                            </td>
                            <td><?= number_format($commande['total'], 2) ?> DA</td>
                            <td>
                                <select class="statut-selector" 
                                        data-order="<?= $commande['id_commande'] ?>"
                                        data-current="<?= $commande['statut'] ?>">
                                    <?php foreach ($statuts_disponibles as $statut): ?>
                                        <option value="<?= $statut ?>" 
                                            <?= $statut === $commande['statut'] ? 'selected' : '' ?>>
                                            <?= formatStatut($statut) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <a href="details.php?id=<?= $commande['id_commande'] ?>" class="action-link">
                                    <i class='bx bx-show'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                   class="<?= $page == $i ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <script>
        // Tri des colonnes
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const sort = header.dataset.sort;
                const dir = '<?= $direction ?>' === 'ASC' ? 'DESC' : 'ASC';
                window.location = `?<?= http_build_query(array_merge($_GET, ['sort' => '_SORT_', 'dir' => '_DIR_'])) ?>`
                    .replace('_SORT_', sort)
                    .replace('_DIR_', dir);
            });
        });

        // Modification du statut
        document.querySelectorAll('.statut-selector').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.order;
                const newStatus = this.value;

                fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        new_status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Statut mis à jour',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.dataset.current = newStatus;
                    } else {
                        Swal.fire('Erreur', data.message, 'error');
                        this.value = this.dataset.current;
                    }
                });
            });
        });
    </script>
</body>
</html>