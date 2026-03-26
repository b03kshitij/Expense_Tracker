<?php
require_once '../config/database.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false, 'message' => 'Not authenticated']); exit; }

$userId = $_SESSION['user_id'];
$amount = floatval($_POST['amount'] ?? 0);
$category = trim($_POST['category'] ?? '');
$date = $_POST['expense_date'] ?? '';
$desc = trim($_POST['description'] ?? '');

if ($amount <= 0 || empty($category) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, description, expense_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isdss", $userId, $category, $amount, $desc, $date);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'message' => 'Expense added successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add expense']);
}
