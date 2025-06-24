<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_commande']) || !is_numeric($_GET['id_commande'])) {
    echo "Commande introuvable.";
    exit();
}

$id_commande = (int) $_GET['id_commande'];

try {

    $stmt = $pdo->prepare("CALL GetCommandeDetails(?)");
    $stmt->execute([$id_commande]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt->nextRowset();
    $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_commande = $totalRow['total_global'];
    $stmt = $pdo->prepare("SELECT statut, date_commande FROM commande WHERE id_commande = ?");
    $stmt->execute([$id_commande]);
    $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

function formatPrice($price) {
    return number_format((float)$price, 2, ',', ' ') . ' DZD';
}

function getStatusInfo($status) {
    switch($status) {
        case 'en_attente':
            return ['text' => 'En attente', 'color' => '#FFA500', 'icon' => 'fas fa-clock'];
        case 'validee':
            return ['text' => 'Validée', 'color' => '#4BB543', 'icon' => 'fas fa-check-circle'];
        case 'annulee':
            return ['text' => 'Annulée', 'color' => '#FF3333', 'icon' => 'fas fa-times-circle'];
        case 'expediee':
            return ['text' => 'Expédiée', 'color' => '#3A86FF', 'icon' => 'fas fa-truck'];
        case 'livree':
            return ['text' => 'Livrée', 'color' => '#6F4E37', 'icon' => 'fas fa-box-open'];
        default:
            return ['text' => ucfirst($status), 'color' => '#666666', 'icon' => 'fas fa-info-circle'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la commande #<?= htmlspecialchars($id_commande) ?> | Coffee Bliss</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6F4E37;
            --primary-light: #B68D40;
            --secondary: #D4B483;
            --light: #F5F5F5;
            --dark: #333333;
            --gray: #777777;
            --danger: #E74C3C;
            --success: #2ECC71;
            --warning: #F39C12;
            --info:rgb(87, 64, 0);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
           
            background-color: #FAFAFA;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container { 
            font-family: 'Poppins', sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .order-details-container {
            background: white;
            border-radius: var(--radius);
            padding: 40px;
            margin: 40px auto;
            box-shadow: var(--shadow);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(111, 78, 55, 0.1);
        }
        
        .order-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary);
            font-weight: 600;
        }
        
        .order-meta {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
        }
        
        .order-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .order-item {
            background-color: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 20px;
            transition: var(--transition);
            border: 1px solid rgba(111, 78, 55, 0.1);
        }
        
        .order-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .no-image {
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 0.8rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .item-meta {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .item-price {
            font-weight: 500;
            color: var(--gray);
        }
        
        .item-total {
            font-weight: 700;
            color: var(--primary);
            margin-top: 10px;
            font-size: 1.1rem;
        }
        
        .order-summary {
            background-color: rgba(111, 78, 55, 0.05);
            padding: 25px;
            border-radius: var(--radius);
            text-align: right;
            margin-top: 40px;
        }
        
        .order-summary h3 {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .order-total {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .back-to-history {
            text-align: center;
            margin-top: 40px;
        }
        
        .back-button {
            background-color: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .back-button:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(111, 78, 55, 0.2);
        }
        
        .empty-order {
            text-align: center;
            padding: 40px 0;
            color: var(--gray);
        }
        
        .empty-order i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--light);
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .order-items {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .order-details-container {
                padding: 25px;
            }
            
            .order-item {
                flex-direction: column;
            }
            
            .product-image {
                width: 100%;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <div class="order-details-container">
            <div class="order-header">
                <div>
                    
                    <p class="order-meta">
                        <?php if (!empty($orderInfo['date_commande'])): ?>
                            <i class="far fa-calendar-alt"></i> <?= date('d/m/Y à H:i', strtotime($orderInfo['date_commande'])) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($orderInfo && !empty($orderInfo['statut'])): 
                    $statusInfo = getStatusInfo($orderInfo['statut']); ?>
                    <span class="order-status" style="background-color: <?= $statusInfo['color'] ?>">
                        <i class="<?= $statusInfo['icon'] ?>"></i>
                        <?= $statusInfo['text'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (count($details) > 0): ?>
                <div class="order-items">
                    <?php foreach ($details as $item): ?>
                        <div class="order-item">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['produit']) ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['produit']) ?></h3>
                                <p class="item-meta">Quantité: <?= htmlspecialchars($item['quantite']) ?></p>
                                <p class="item-price">Prix unitaire: <?= formatPrice($item['prix_unitaire']) ?></p>
                                <p class="item-total">Total: <?= formatPrice($item['total_par_produit']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <h3>Total de la commande</h3>
                    <p class="order-total"><?= formatPrice($total_commande) ?></p>
                </div>
            <?php else: ?>
                <div class="empty-order">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun produit trouvé pour cette commande</p>
                </div>
            <?php endif; ?>

            <div class="back-to-history">
                <a href="history.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour à l'historique
                </a>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.order-item');
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>