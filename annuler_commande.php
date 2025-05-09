<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$commande_id = (int)$_POST['commande_id'];

try {
    $stmt = $pdo->prepare("
        UPDATE commande 
        SET statut = 'annulee' 
        WHERE id_commande = ? 
        AND id_utilisateur = ? 
        AND statut != 'annulee'
    ");
    $stmt->execute([$commande_id, $_SESSION['user_id']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Commande introuvable ou déjà annulée.");
    }

    $_SESSION['success'] = "Commande #$commande_id annulée avec succès.";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: history.php");
exit();
