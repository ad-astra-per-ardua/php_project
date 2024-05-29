<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = htmlspecialchars($_POST['username']);

    $apiUrlProblems = 'http://localhost:3000/problems?limit=100';
    $responseProblems = file_get_contents($apiUrlProblems);
    $dataProblems = json_decode($responseProblems, true);

    if (!is_array($dataProblems) || !isset($dataProblems['problemsetQuestionList'])) {
        die('Error: Invalid API response for problems');
    }

    $problems = $dataProblems['problemsetQuestionList'];

    $apiUrlSubmissions = "http://localhost:3000/$username/submission";
    $responseSubmissions = @file_get_contents($apiUrlSubmissions);

    if ($responseSubmissions === FALSE) {
        die('Error: Could not fetch data from API');
    }

    $dataSubmissions = json_decode($responseSubmissions, true);

    if (!is_array($dataSubmissions) || !isset($dataSubmissions['submission'])) {
        die('Error: Invalid API response for submissions');
    }

    $submissions = $dataSubmissions['submission'];

    $apiUrlAcSubmissions = "http://localhost:3000/$username/acSubmission";
    $responseAcSubmissions = @file_get_contents($apiUrlAcSubmissions);

    if ($responseAcSubmissions === FALSE) {
        die('Error: Could not fetch data from API');
    }

    $dataAcSubmissions = json_decode($responseAcSubmissions, true);

    if (!is_array($dataAcSubmissions) || !isset($dataAcSubmissions['submission'])) {
        die('Error: Invalid API response for accepted submissions');
    }

    $acSubmissions = $dataAcSubmissions['submission'];

    $stmtProblem = $pdo->prepare('INSERT INTO problems (title, title_slug, difficulty) VALUES (?, ?, ?)
                                 ON DUPLICATE KEY UPDATE difficulty = VALUES(difficulty)');
    $stmtProblemId = $pdo->prepare('SELECT id FROM problems WHERE title_slug = ?');
    $stmtTag = $pdo->prepare('INSERT INTO problem_tags (problem_id, tag) VALUES (?, ?)');

    foreach ($problems as $problem) {
        if (is_array($problem) && isset($problem['title'], $problem['titleSlug'], $problem['difficulty'], $problem['topicTags'])) {
            $stmtProblem->execute([$problem['title'], $problem['titleSlug'], $problem['difficulty']]);
            $stmtProblemId->execute([$problem['titleSlug']]);
            $problemId = $stmtProblemId->fetchColumn();

            foreach ($problem['topicTags'] as $tag) {
                $stmtTag->execute([$problemId, $tag['name']]);
            }
        }
    }

    // Save submissions to the database
    $stmtCheckSubmission = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE username = ? AND timestamp = ?');
    $stmtSubmission = $pdo->prepare('INSERT INTO submissions (username, title, title_slug, status, timestamp, lang, submission_id) VALUES (?, ?, ?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE status = VALUES(status), timestamp = VALUES(timestamp), lang = VALUES(lang)');

    foreach ($submissions as $submission) {
        if (is_array($submission) && isset($submission['title'], $submission['titleSlug'], $submission['statusDisplay'], $submission['timestamp'], $submission['lang'])) {
            $stmtCheckSubmission->execute([$username, $submission['timestamp']]);
            $count = $stmtCheckSubmission->fetchColumn();

            if ($count == 0) {
                $stmtSubmission->execute([$username, $submission['title'], $submission['titleSlug'], $submission['statusDisplay'], $submission['timestamp'], $submission['lang'], $submission['timestamp']]);
            }
        }
    }

    // Save accepted submissions to the database
    $stmtCheckAcSubmission = $pdo->prepare('SELECT COUNT(*) FROM problems WHERE title_slug = ? AND status = "Accepted"');
    $stmtAcSubmission = $pdo->prepare('INSERT INTO problems (title, title_slug, difficulty, status) VALUES (?, ?, ?, "Accepted")
                                      ON DUPLICATE KEY UPDATE status = VALUES(status)');

    foreach ($acSubmissions as $acSubmission) {
        if (is_array($acSubmission) && isset($acSubmission['title'], $acSubmission['titleSlug'], $acSubmission['difficulty'])) {
            $stmtCheckAcSubmission->execute([$acSubmission['titleSlug']]);
            $count = $stmtCheckAcSubmission->fetchColumn();

            if ($count == 0) {
                $stmtAcSubmission->execute([$acSubmission['title'], $acSubmission['titleSlug'], $acSubmission['difficulty']]);
            }
        }
    }

    header('Location: dashboard.php?username=' . urlencode($username));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LeetCode User Input</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input[type="text"] {
            padding: 10px;
            width: 80%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enter LeetCode Username</h1>
        <form action="index.php" method="POST">
            <input type="text" name="username" placeholder="Enter LeetCode Username" value="per_ardua_ad_astra" required>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
