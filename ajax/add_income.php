<?php
header('Content-Type: application/json');


session_start();
require_once '../config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;

$amount = $_POST['amount'] ?? '';
$source = $_POST['source'] ?? '';
$date = $_POST['income_date'] ?? '';
$desc = $_POST['description'] ?? '';

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO income (user_id, amount, source, description, income_date) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$stmt->bind_param("idsss", $user_id, $amount, $source, $desc, $date);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}