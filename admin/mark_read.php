<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Verify valid request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        markMessageAsRead($pdo, (int)$_GET['id']);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>