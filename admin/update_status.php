<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
    $stmt->execute([$data['new_status'], $data['order_id']]);
    
    echo json_encode(['success' => $stmt->rowCount() > 0]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
} ?>