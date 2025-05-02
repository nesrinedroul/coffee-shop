
function getSystemMessages($pdo) {
    $stmt = $pdo->query("SELECT * FROM system_messages ORDER BY date_creation DESC LIMIT 10");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markMessageAsRead($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE system_messages SET lu = 1 WHERE id_message = ?");
    $stmt->execute([$id]);
}