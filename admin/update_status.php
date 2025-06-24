<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permissions insuffisantes']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
    exit();
}

if (!isset($data['new_status'], $data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

$statuts_valides = ['en_attente', 'validee', 'annulee', 'en_cours', 'livree']; 
if (!in_array($data['new_status'], $statuts_valides)) {
    http_response_code(422);
    echo json_encode([
        'success' => false, 
        'message' => 'Statut invalide',
        'statut_reçu' => $data['new_status'],
        'statuts_valides' => $statuts_valides
    ]);
    exit();
}

try {
    
    $checkStmt = $pdo->prepare("SELECT id_commande FROM commande WHERE id_commande = ?");
    $checkStmt->execute([$data['order_id']]);
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
        exit();
    }

  
    $updateStmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
    $updateStmt->execute([$data['new_status'], $data['order_id']]);
    error_log("Tentative de mise à jour commande #{$data['order_id']} vers {$data['new_status']}");

    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Statut mis à jour',
            'order_id' => $data['order_id'],
            'new_status' => $data['new_status']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Aucune modification effectuée',
            'possible_reason' => 'Le statut était déjà à cette valeur'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erreur DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur de base de données',
        'error_details' => $e->getMessage()
    ]);
}