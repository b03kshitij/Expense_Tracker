<?php
require_once '../config/database.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false, 'message' => 'Not authenticated']); exit; }

$userId = $_SESSION['user_id'];
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Expense deleted!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete']);
}
