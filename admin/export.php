<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$mot_cle = $_GET['mot_cle'] ?? null;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=commandes_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'ID Commande', 'Date', 'Client', 'Total', 'Statut', 
    'Produits', 'Quantité', 'Prix Unitaire'
]);

try {
    $stmt = $pdo->prepare("CALL ListerDetailsCommandes()");
    $stmt->execute([]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id_commande'],
            $row['date_commande'],
            $row['prenom'] . ' ' . $row['nom'],
            $row['total'],
            $row['statut'],
            $row['nom_produit'],
            $row['quantite'],
            $row['prix_unitaire']
        ]);
    }

} catch (PDOException $e) {
    die("Erreur lors de l'export : " . $e->getMessage());
}

fclose($output);
exit;
?>