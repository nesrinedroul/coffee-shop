<?php
session_start();
require 'includes/db.php';

// Vérifier que la requête est bien POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: cart.php");
    exit();
}

// Vérifier la connexion utilisateur
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Connectez-vous pour finaliser la commande";
    header("Location: login.php");
    exit();
}
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Votre panier est vide";
    header("Location: cart.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // Étape 1 : Création de la commande
    $stmt = $pdo->prepare("INSERT INTO commande (id_utilisateur, date_commande, statut, total) 
                          VALUES (:user_id, NOW(), 'en_attente', 0)");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $commande_id = $pdo->lastInsertId();

    // Étape 2 : Insertion des produits
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $pdo->prepare("INSERT INTO commande_produit (id_commande, id_produit, quantite) 
                              VALUES (:commande_id, :product_id, :quantity)");
        $stmt->execute([
            ':commande_id' => $commande_id,
            ':product_id' => $product_id,
            ':quantity' => $item['quantity']
        ]);
    }

    // Étape 3 : Calcul du total
    $stmt = $pdo->prepare("UPDATE commande c
                          SET total = (
                              SELECT SUM(cp.quantite * p.prix)
                              FROM commande_produit cp
                              JOIN produit p ON cp.id_produit = p.id_produit
                              WHERE cp.id_commande = :commande_id
                          )
                          WHERE c.id_commande = :commande_id");
    $stmt->execute([':commande_id' => $commande_id]);

    $pdo->commit();

    // Réinitialiser le panier
    unset($_SESSION['cart']);
    $_SESSION['success'] = "Commande #$commande_id validée avec succès!";
    header("Location: confirmation.php?id=$commande_id");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    
    // Gestion des erreurs spécifiques
    $error_message = $e->getMessage();
    
    if (strpos($error_message, 'Stock insuffisant') !== false) {
        // Extraire les détails de l'erreur
        preg_match('/Stock insuffisant pour le produit #(\d+) \(Disponible: (\d+), Demandé: (\d+)\)/', $error_message, $matches);
        
        if (!empty($matches)) {
            $product_id = $matches[1];
            $stock = $matches[2];
            $requested = $matches[3];
            
            $_SESSION['error'] = "Stock insuffisant pour le produit #$product_id. Disponible: $stock, Demandé: $requested";
        } else {
            $_SESSION['error'] = "Erreur de stock : " . $error_message;
        }
    } else {
        $_SESSION['error'] = "Erreur technique : " . $error_message;
    }
    
    header("Location: cart.php");
    exit();
}