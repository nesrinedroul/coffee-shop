<?php
session_start();
include('includes/db.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produit non trouvé.";
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "Produit introuvable.";
    exit;
}

function getCurrentUserId($pdo) {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    if (isset($_SESSION['email'])) {
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
        $stmt->execute([$_SESSION['email']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            return $user['id_utilisateur'];
        }
    }
    
    return null;
}

// Get average rating and count
$ratingStmt = $pdo->prepare("
    SELECT AVG(note) as moyenne, COUNT(*) as total_notes 
    FROM produit_notation 
    WHERE id_produit = ?
");
$ratingStmt->execute([$id]);
$ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);

$ratingDistStmt = $pdo->prepare("
    SELECT note, COUNT(*) as nombre
    FROM produit_notation
    WHERE id_produit = ?
    GROUP BY note
    ORDER BY note DESC
");
$ratingDistStmt->execute([$id]);
$ratingDistribution = $ratingDistStmt->fetchAll(PDO::FETCH_ASSOC);

$commentsStmt = $pdo->prepare("
    SELECT pc.*, u.nom, u.prenom
    FROM produit_commentaire pc
    JOIN utilisateur u ON pc.id_utilisateur = u.id_utilisateur
    WHERE pc.id_produit = ?
    ORDER BY pc.date_commentaire DESC
");
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['username'])) {
        $_SESSION['redirect_after_login'] = "details.php?id=" . $id;
        $_SESSION['error_message'] = "Veuillez vous connecter pour effectuer cette action.";
        header("Location: login.php");
        exit;
    }
    
    $userId = getCurrentUserId($pdo);
    if (!$userId) {
        $_SESSION['error_message'] = "Erreur d'authentification. Veuillez vous reconnecter.";
        header("Location: login.php");
        exit;
    }
    
    if (isset($_POST['id_produit']) && !isset($_POST['submit_rating']) && !isset($_POST['submit_comment'])) {
        $productId = (int)$_POST['id_produit'];
        $quantity = (int)$_POST['quantite'];
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity
            ];
        }
        header("Location: cart.php");
        exit();
    }
    
    if (isset($_POST['submit_rating'])) {
        $rating = (int)$_POST['rating'];
        
        try {
            if ($rating < 1 || $rating > 5) {
                throw new Exception("La note doit être entre 1 et 5");
            }
            
            $stmt = $pdo->prepare("CALL AjouterNotation(?, ?, ?)");
            $stmt->execute([$id, $userId, $rating]);
            
            $_SESSION['success_message'] = "Votre note a été enregistrée avec succès!";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        }
        
        header("Location: details.php?id=" . $id);
        exit();
    }
    
    if (isset($_POST['submit_comment'])) {
        $comment = trim($_POST['comment']);
        
        try {
            if (empty($comment)) {
                throw new Exception("Le commentaire ne peut pas être vide");
            }
            $stmt = $pdo->prepare("
                INSERT INTO produit_commentaire 
                (id_produit, id_utilisateur, commentaire, date_commentaire) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$id, $userId, $comment]);
            
            $_SESSION['success_message'] = "Votre commentaire a été publié avec succès!";
            header("Location: details.php?id=" . $id);
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
            header("Location: details.php?id=" . $id);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom']) ?> - Coffee Bliss</title>
    <link rel="stylesheet" href="assets/css/details.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<div class="notification-container" id="notificationContainer">
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="notification notification-error">
                <span>'.htmlspecialchars($_SESSION['error_message']).'</span>
                <button class="notification-close">&times;</button>
              </div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
        echo '<div class="notification notification-success">
                <span>'.htmlspecialchars($_SESSION['success_message']).'</span>
                <button class="notification-close">&times;</button>
              </div>';
        unset($_SESSION['success_message']);
    }
    ?>
</div>

<main class="product-detail-container">
    <div class="product-image">
        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
        <?php if ($produit['stock'] > 10): ?>
            <span class="status-badge in-stock"><i class="fas fa-check"></i> En stock</span>
        <?php elseif ($produit['stock'] > 0): ?>
            <span class="status-badge low-stock"><i class="fas fa-exclamation"></i> Stock limité</span>
        <?php else: ?>
            <span class="status-badge out-of-stock"><i class="fas fa-times"></i> Rupture</span>
        <?php endif; ?>
    </div>
    
    <div class="product-info">
        <h1><?= htmlspecialchars($produit['nom']) ?></h1>
          
        <p class="description"><?= nl2br(htmlspecialchars($produit['description'])) ?></p>
        
        <div class="price-section">
            <p class="price"><?= number_format($produit['prix'], 2) ?> DZD</p>
            <p class="shipping"><i class="fas fa-truck"></i> Livraison gratuite</p>
        </div>
        
        <?php if (isset($_SESSION['username']) && $produit['stock'] > 0): ?>
            <form method="post" class="add-to-cart-form">
                <div class="quantity-selector">
                    <label for="quantite">Quantité :</label>
                    <div class="quantity-control">
                        <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantite" id="quantite" value="1" min="1" max="<?= $produit['stock'] ?>">
                        <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                <button type="submit" class="btn-ajouter">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </button>
            </form>
        <?php elseif ($produit['stock'] <= 0): ?>
            <p class="warning-message">Ce produit n'est pas disponible pour le moment</p>
        <?php else: ?>
            <p class="warning-message">
                <i class="fas fa-info-circle"></i> Veuillez <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">vous connecter</a> pour commander ce produit.
            </p>
        <?php endif; ?>
        
        <div class="product-details">
            <h3><i class="fas fa-info-circle"></i> Détails du produit</h3>
            <ul>
                <li><strong>Stock disponible :</strong> <?= $produit['stock'] ?> unités</li>
            </ul>
        </div>

        <!-- Rating Section -->
        <section class="rating-section">
            <h2><i class="fas fa-star"></i> Évaluations</h2>
            
            <div class="average-rating">
                <div class="stars">
                    <?php
                    $avgRating = round($ratingData['moyenne'] ?? 0, 1);
                    $fullStars = floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                    
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="fas fa-star" style="color: #FFA41C;"></i>';
                        } elseif ($hasHalfStar && $i == $fullStars + 1) {
                            echo '<i class="fas fa-star-half-alt" style="color: #FFA41C;"></i>';
                        } else {
                            echo '<i class="far fa-star" style="color: #FFA41C;"></i>';
                        }
                    }
                    ?>
                </div>
                <span class="rating-value"><?= number_format($avgRating, 1) ?> sur 5</span>
                <span class="rating-count">(<?= $ratingData['total_notes'] ?? 0 ?> avis)</span>
            </div>
            
            <?php if (($ratingData['total_notes'] ?? 0) > 0): ?>
            <div class="rating-distribution">
                <?php
                $totalRatings = $ratingData['total_notes'];
                for ($i = 5; $i >= 1; $i--) {
                    $count = 0;
                    foreach ($ratingDistribution as $dist) {
                        if ($dist['note'] == $i) {
                            $count = $dist['nombre'];
                            break;
                        }
                    }
                    $percentage = ($count / $totalRatings) * 100;
                    ?>
                    <div class="rating-bar">
                        <span class="rating-value-label"><?= $i ?> étoiles</span>
                        <div class="rating-bar-container">
                            <div class="rating-bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="rating-count-label"><?= $count ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php else: ?>
                <p>Aucune évaluation pour le moment.</p>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['username'])): ?>
            <form method="post" class="rating-form">
                <label>Votre note :</label>
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>">
                        <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
                <button type="submit" name="submit_rating" class="btn-submit">Noter</button>
            </form>
            <?php else: ?>
                <p class="login-prompt">Connectez-vous pour noter ce produit</p>
            <?php endif; ?>
        </section>

        <section class="comments-section">
    <h2><i class="fas fa-comments"></i> Commentaires (<?= count($comments) ?>)</h2>
    
    <div class="comments-list <?= count($comments) > 3 ? '' : 'expanded' ?>" id="commentsList">
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $index => $comment): ?>
                <div class="comment">
                    <div class="comment-author">
                        <?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?>
                        <span class="comment-date">
                            - <?= date('d/m/Y H:i', strtotime($comment['date_commentaire'])) ?>
                        </span>
                    </div>
                    <div class="comment-text">
                        <?= nl2br(htmlspecialchars($comment['commentaire'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun commentaire pour le moment.</p>
        <?php endif; ?>
    </div>
    
    <?php if (count($comments) > 3): ?>
        <button class="show-more-btn" id="showMoreBtn">
            Voir plus de commentaires <i class="fas fa-chevron-down"></i>
        </button>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['username'])): ?>
    <form method="post" class="comment-form">
        <label for="comment">Ajouter un commentaire :</label>
        <textarea name="comment" id="comment" rows="4" required></textarea>
        <button type="submit" name="submit_comment" class="btn-submit">Envoyer</button>
    </form>
    <?php else: ?>
        <p class="login-prompt">Connectez-vous pour laisser un commentaire</p>
    <?php endif; ?>
</section>
    </div>
</main>

<?php include('includes/footer.php'); ?>

<script>
// Quantity Selector
document.querySelectorAll('.qty-btn').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        let value = parseInt(input.value);
        
        if (button.classList.contains('minus') && value > 1) {
            input.value = value - 1;
        } else if (button.classList.contains('plus') && value < <?= $produit['stock'] ?>) {
            input.value = value + 1;
        }
    });
});

// Star Rating Interaction
document.querySelectorAll('.star-rating label').forEach(label => {
    label.addEventListener('click', () => {
        const radio = label.previousElementSibling;
        radio.checked = true;
    });
});

// Floating Notifications
document.addEventListener('DOMContentLoaded', function() {
    // Show notifications with animation
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach((notification, index) => {
        setTimeout(() => {
            notification.classList.add('show');
        }, 100 * index);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    });
    
    // Auto-remove notifications after 5 seconds
    setTimeout(() => {
        notifications.forEach(notification => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    }, 5000);
    
    // Show more comments functionality
    const showMoreBtn = document.getElementById('showMoreBtn');
    const commentsList = document.getElementById('commentsList');
    
    if (showMoreBtn && commentsList) {
        let isExpanded = false;
        
        showMoreBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                commentsList.classList.add('expanded');
                showMoreBtn.innerHTML = 'Voir moins <i class="fas fa-chevron-up"></i>';
                showMoreBtn.classList.add('expanded');
            } else {
                commentsList.classList.remove('expanded');
                showMoreBtn.innerHTML = 'Voir plus de commentaires <i class="fas fa-chevron-down"></i>';
                showMoreBtn.classList.remove('expanded');
                
                // Scroll to comments section when collapsing
                setTimeout(() => {
                    commentsList.scrollIntoView({ behavior: 'smooth' });
                }, 300);
            }
        });
    }
});
</script>
</body>
</html>