<?php
global $pdo;
include 'config/database.php';

if (!isset($_GET['titleSlug'])) {
    die('Error: Title slug not provided');
}

$titleSlug = htmlspecialchars($_GET['titleSlug']);

$stmt = $pdo->prepare('SELECT memo FROM problems WHERE title_slug = ?');
$stmt->execute([$titleSlug]);
$problem = $stmt->fetch(PDO::FETCH_ASSOC);

$memo = $problem ? $problem['memo'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Delete memo
        $stmt = $pdo->prepare('UPDATE problems SET memo = NULL WHERE title_slug = ?');
        $stmt->execute([$titleSlug]);

        echo "<script>window.opener.updateMemoLabel('$titleSlug', 'Memo'); window.close();</script>";
        exit;
    } elseif (isset($_POST['memo'])) {
        // Save memo
        $memo = htmlspecialchars($_POST['memo']);

        $stmt = $pdo->prepare('UPDATE problems SET memo = ? WHERE title_slug = ?');
        $stmt->execute([$memo, $titleSlug]);

        echo "<script>window.opener.updateMemoLabel('$titleSlug', 'Show Memo'); window.close();</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Memo for <?= htmlspecialchars($titleSlug) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 600px;
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
        .button-group {
            display: flex;
            justify-content: center;
        }
        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            margin-bottom: 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
        }
        form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Memo for <?= htmlspecialchars($titleSlug) ?></h1>
        <form action="memo.php?titleSlug=<?= htmlspecialchars($titleSlug) ?>" method="POST">
            <textarea name="memo" rows="10" cols="30"><?= htmlspecialchars($memo) ?></textarea><br>
            <div class="button-group">
                <button type="submit">Save</button>
                <button type="submit" name="delete" value="1">Delete</button>
            </div>
        </form>
    </div>
</body>
</html>
