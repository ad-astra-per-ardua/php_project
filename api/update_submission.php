<?php
include '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$status = $data['status'];
$title = $data['title'];

$stmt = $pdo->prepare("INSERT INTO submissions (username, title, status) VALUES (?, ?, ?)");
$stmt->execute([$username, $title, $status]);

echo json_encode(['status' => 'success']);
?>
