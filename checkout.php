<?php
session_start();
include('includes/db.php');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier que le panier n'est pas vide
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    echo "Votre panier est vide.";
    exit();
}

// Initialiser le total
$total = 0;
$commandeProduits = [];

// Calculer le total et préparer les données
foreach ($_SESSION['cart'] as $productId => $item) {
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = :id");
    $stmt->execute([':id' => $productId]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) continue;

    $prixUnitaire = $produit['prix'];
    $quantite = $item['quantity'];
    $total += $prixUnitaire * $quantite;

    $commandeProduits[] = [
        'id_produit' => $productId,
        'quantite' => $quantite,
        'prix_unitaire' => $prixUnitaire
    ];
}

// Enregistrer la commande dans la base
$stmt = $pdo->prepare("INSERT INTO commande (id_utilisateur, date_commande, statut, total) VALUES (:id_user, NOW(), 'en_attente', :total)");
$stmt->execute([
    ':id_user' => $_SESSION['user_id'],
    ':total' => $total
]);

// Récupérer l'ID de la commande nouvellement créée
$id_commande = $pdo->lastInsertId();

// Insérer les produits de la commande
foreach ($commandeProduits as $item) {
    $stmt = $pdo->prepare("INSERT INTO commande_produit (id_commande, id_produit, quantite, prix_unitaire) 
                           VALUES (:id_commande, :id_produit, :quantite, :prix_unitaire)");
    $stmt->execute([
        ':id_commande' => $id_commande,
        ':id_produit' => $item['id_produit'],
        ':quantite' => $item['quantite'],
        ':prix_unitaire' => $item['prix_unitaire']
    ]);
}

// Vider le panier après validation
unset($_SESSION['cart']);

// Rediriger vers une page de confirmation
header("Location: confirmation.php?commande=$id_commande");
exit();
?>
