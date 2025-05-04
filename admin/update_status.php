<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validation basique
if (!isset($data['new_status'], $data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

$statuts_valides = ['en_attente', 'validee', 'annulee'];
if (!in_array($data['new_status'], $statuts_valides)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Statut invalide']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
    $stmt->execute([$data['new_status'], $data['order_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune mise à jour effectuée']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
}
?>
