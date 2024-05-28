<?php
header('Content-Type: application/json');
$apiUrl = 'http://localhost:3000/problems?limit=20';

$response = file_get_contents($apiUrl);
if ($response === FALSE) {
    echo json_encode(['error' => 'Failed to fetch problem data']);
    exit;
}

$problems = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response']);
    exit;
}

echo json_encode($problems);
?>
