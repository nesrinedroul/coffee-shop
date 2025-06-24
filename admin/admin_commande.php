<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$mot_cle = !empty($_GET['mot_cle']) ? $_GET['mot_cle'] : null;
$statut = !empty($_GET['statut']) ? $_GET['statut'] : null;
$date_debut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$date_fin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;
$page = max(1, $_GET['page'] ?? 1);
$per_page = 20;

$statuts_disponibles = ['en_attente', 'validee', 'annulee'];

try {
    $stmt = $pdo->prepare("CALL ListerCommandesAdminFiltre(?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mot_cle, $statut, $date_debut, $date_fin, $page, $per_page]);

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

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
    <title>Tableau de Bord - Commandes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        :root {
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --white: #ffffff;
            --border-radius: 0.375rem;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }

        body {
            margin:27px;
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .page-header {
            padding:10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }
        .filter-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
            align-items: end;
        }


        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        .data-table {
            width: 100%;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .data-table thead {
            background-color: var(--primary);
            color: var(--white);
        }

        .data-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.875rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3bf;
            color: #e67700;
        }

        .status-approved {
            background-color: #d3f9d8;
            color: #2b8a3e;
        }

        .status-cancelled {
            background-color: #ffe3e3;
            color: #c92a2a;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: transparent;
            color: var(--gray);
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            background-color: #f1f5f9;
            color: var(--primary);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .page-item {
            list-style: none;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius);
            background-color: var(--white);
            color: var(--gray);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
        }

        .page-link:hover, .page-link.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="main">
        <?php include('admin_header.php'); ?>
        
        <div class="main-content main">
            <div class="page-header">
                <h1 class="page-title">Gestion des Commandes</h1>
                <div class="actions">
                <a href="export.php" class="action-btn" title="Imprimer">
                                                <i class='bx bx-printer'></i>importer
                                            </a>
                </div>
            </div>
            
            <div class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label for="search">Recherche</label>
                            <input type="text" id="search" name="mot_cle" placeholder="ID, client..." value="<?= htmlspecialchars($mot_cle ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut</label>
                            <select id="status" name="statut">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($statuts_disponibles as $s): ?>
                                    <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>>
                                        <?= formatStatut($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_start">Date début</label>
                            <input type="date" id="date_start" name="date_debut" value="<?= htmlspecialchars($date_debut ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_end">Date fin</label>
                            <input type="date" id="date_end" name="date_fin" value="<?= htmlspecialchars($date_fin ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="height: 42px;">
                                <i class='bx bx-filter-alt'></i> Filtrer
                            </button>
                            <a href="?" class="btn btn-outline" style="height: 42px; margin-left: 0.5rem;">
                                Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Statut</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($commandes)): ?>
                            <?php foreach ($commandes as $commande): ?>
                                <tr>
                                    <td>#<?= $commande['id_commande'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                                    <td><?= htmlspecialchars($commande['prenom']) . ' ' . htmlspecialchars($commande['nom']) ?></td>
                                    <td><?= number_format($commande['total'], 2) ?> DA</td>
                                    <td>
                                        <select class="status-select"
                                                data-order="<?= $commande['id_commande'] ?>"
                                                data-current="<?= $commande['statut'] ?>"
                                                onchange="updateOrderStatus(this)">
                                            <?php foreach ($statuts_disponibles as $s): ?>
                                                <option value="<?= $s ?>" <?= $s === $commande['statut'] ? 'selected' : '' ?>>
                                                    <?= formatStatut($s) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">
                                    Aucune commande trouvée pour les critères sélectionnés.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination">
                <?php for ($i = 1; $i <= $page + 1; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                       class="page-link <?= $i == $page ? 'active' : '' ?>">
                       <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function updateOrderStatus(selectElement) {
        const orderId = selectElement.dataset.order;
        const currentStatus = selectElement.dataset.current;
        const newStatus = selectElement.value;
        
        if (newStatus === currentStatus) {
            selectElement.value = currentStatus;
            return;
        }
        
        Swal.fire({
            title: 'Confirmer la modification',
            text: `Voulez-vous vraiment changer le statut de la commande #${orderId} en "${newStatus}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirmer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#4361ee',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('update_status.php', {
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
                        Swal.fire({
                            title: 'Succès!',
                            text: 'Le statut a été mis à jour.',
                            icon: 'success',
                            confirmButtonColor: '#4361ee',
                        });
                        selectElement.dataset.current = newStatus;
                    } else {
                        throw new Error(data.message || 'Erreur lors de la mise à jour');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Erreur!',
                        text: error.message,
                        icon: 'error',
                        confirmButtonColor: '#4361ee',
                    });
                    selectElement.value = currentStatus;
                });
            } else {
                selectElement.value = currentStatus;
            }
        });
    }
    </script>
</body>
</html>