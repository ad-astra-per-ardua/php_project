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
</head>
<body>
    <h1>Memo for <?= htmlspecialchars($titleSlug) ?></h1>
    <form action="memo.php?titleSlug=<?= htmlspecialchars($titleSlug) ?>" method="POST">
        <textarea name="memo" rows="10" cols="30"><?= htmlspecialchars($memo) ?></textarea><br>
        <button type="submit">Save</button>
        <button type="submit" name="delete" value="1">Delete</button>
    </form>
</body>
</html>
