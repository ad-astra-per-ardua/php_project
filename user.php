<?php
include 'config/database.php';

if (!isset($_GET['username'])) {
    die('Error: Username not provided');
}

$username = htmlspecialchars($_GET['username']);

// Fetch user submissions from the API
$apiUrl = "http://localhost:3000/$username/submission?limit=20";
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    die('Error: Could not fetch data from API');
}

$data = json_decode($response, true);

// Check if the API response is valid and contains submissions
if (!is_array($data) || !isset($data['submission'])) {
    die('Error: Invalid API response');
}

$submissions = $data['submission'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Submissions</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1><?= htmlspecialchars($username) ?>'s Submissions</h1>
    <ul>
        <?php foreach ($submissions as $submission): ?>
            <?php if (is_array($submission) && isset($submission['title'], $submission['statusDisplay'])): ?>
                <li>
                    <?= htmlspecialchars($submission['title']) ?> -
                    <?= htmlspecialchars($submission['statusDisplay'] == 'Accepted' ? '✔' : '✘') ?>
                </li>
            <?php else: ?>
                <li>Error: Submission data is invalid</li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</body>
</html>
