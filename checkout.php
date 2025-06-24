<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: cart.php");
    exit();
}

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
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

    $stmt = $pdo->prepare("INSERT INTO commande (id_utilisateur, date_commande, statut, total) 
                           VALUES (:user_id, NOW(), 'en_attente', 0)");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $commande_id = $pdo->lastInsertId();
    foreach ($_SESSION['cart'] as $product_id => $item) {

        $stmt = $pdo->prepare("SELECT prix FROM produit WHERE id_produit = :product_id");
        $stmt->execute([':product_id' => $product_id]);
        $prix_unitaire = $stmt->fetchColumn();

        if ($prix_unitaire === false || $prix_unitaire === null) {
            throw new Exception("Erreur : le produit #$product_id est introuvable ou son prix est invalide.");
        }

        $stmt = $pdo->prepare("INSERT INTO commande_produit (id_commande, id_produit, quantite, prix_unitaire) 
                               VALUES (:commande_id, :product_id, :quantity, :prix_unitaire)");
        $stmt->execute([
            ':commande_id' => $commande_id,
            ':product_id' => $product_id,
            ':quantity' => $item['quantity'],
            ':prix_unitaire' => $prix_unitaire
        ]);
    }
    $stmt = $pdo->prepare("UPDATE commande
                           SET total = (
                               SELECT SUM(quantite * prix_unitaire)
                               FROM commande_produit
                               WHERE id_commande = :commande_id
                           )
                           WHERE id_commande = :commande_id");
    $stmt->execute([':commande_id' => $commande_id]);

    $pdo->commit();
    unset($_SESSION['cart']);
   
    header("Location: confirmation.php?id=$commande_id");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();

    $error_message = $e->getMessage();
    if (strpos($error_message, 'Stock insuffisant') !== false) {
        preg_match('/Stock insuffisant pour le produit #(\d+) \(Disponible: (\d+), Demandé: (\d+)\)/', $error_message, $matches);
        if (!empty($matches)) {
            $product_id = $matches[1];
            $stock = $matches[2];
            $requested = $matches[3];
            $_SESSION['error'] = "Stock insuffisant pour le produit #$product_id. Disponible : $stock, demandé : $requested";
        } else {
            $_SESSION['error'] = "Erreur de stock : " . $error_message;
        }
    } else {
        $_SESSION['error'] = "Erreur technique : " . $error_message;
    }

    header("Location: cart.php");
    exit();
}
