<?php
require_once '../config/database.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false, 'message' => 'Not authenticated']); exit; }

$userId = $_SESSION['user_id'];
$id = intval($_POST['id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$category = trim($_POST['category'] ?? '');
$date = $_POST['expense_date'] ?? '';
$desc = trim($_POST['description'] ?? '');

if ($id <= 0 || $amount <= 0 || empty($category) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$stmt = $conn->prepare("UPDATE expenses SET category = ?, amount = ?, description = ?, expense_date = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sdssii", $category, $amount, $desc, $date, $id, $userId);

if ($stmt->execute() && $stmt->affected_rows >= 0) {
    echo json_encode(['success' => true, 'message' => 'Expense updated!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
