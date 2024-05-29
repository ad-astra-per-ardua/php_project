<?php
include 'config/database.php';

if (!isset($_GET['username'])) {
    die('Error: Username not provided');
}

$username = htmlspecialchars($_GET['username']);

$tags = $pdo->query('SELECT DISTINCT tag FROM problem_tags')->fetchAll(PDO::FETCH_COLUMN);

$filteredTag = isset($_GET['tag']) ? htmlspecialchars($_GET['tag']) : '';
$pageProblems = isset($_GET['pageProblems']) ? (int)$_GET['pageProblems'] : 1;
$pageSubmissions = isset($_GET['pageSubmissions']) ? (int)$_GET['pageSubmissions'] : 1;
$limit = 20;
$offsetProblems = ($pageProblems - 1) * $limit;
$offsetSubmissions = ($pageSubmissions - 1) * $limit;

if ($filteredTag) {
    $stmtProblems = $pdo->prepare('SELECT p.* FROM problems p INNER JOIN problem_tags t ON p.id = t.problem_id WHERE t.tag = ? LIMIT ? OFFSET ?');
    $stmtProblems->execute([$filteredTag, $limit, $offsetProblems]);
} else {
    $stmtProblems = $pdo->prepare('SELECT * FROM problems LIMIT ? OFFSET ?');
    $stmtProblems->execute([$limit, $offsetProblems]);
}

$problems = $stmtProblems->fetchAll(PDO::FETCH_ASSOC);

$stmtSubmissions = $pdo->prepare('SELECT * FROM submissions WHERE username = ? ORDER BY timestamp DESC LIMIT ? OFFSET ?');
$stmtSubmissions->execute([$username, $limit, $offsetSubmissions]);
$submissions = $stmtSubmissions->fetchAll(PDO::FETCH_ASSOC);

$submissionMap = [];
foreach ($submissions as $submission) {
    $submissionMap[$submission['title_slug']] = $submission['status'];
}

$totalProblems = $pdo->query('SELECT COUNT(*) FROM problems')->fetchColumn();
$totalPagesProblems = ceil($totalProblems / $limit);

$totalSubmissions = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE username = ?');
$totalSubmissions->execute([$username]);
$totalSubmissions = $totalSubmissions->fetchColumn();
$totalPagesSubmissions = ceil($totalSubmissions / $limit);

function formatTimestamp($timestamp) {
    return date("Y-m-d H:i:s", $timestamp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($username) ?>'s Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            padding: 10px;
        }
        nav ul li a:hover {
            text-decoration: underline;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            color: #007bff;
            text-decoration: none;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        .form-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .form-container form {
            display: flex;
            align-items: center;
        }
        .form-container select,
        .form-container button {
            margin-left: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: white;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        ul li a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        ul li button:hover {
            background-color: #0056b3;
        }
        .status-accepted {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .status-icon {
            font-size: 18px;
            margin-right: 10px;
        }
        .status-icon.accepted {
            color: green;
        }
        .status-icon.rejected {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($username) ?>'s Dashboard</h1>
        <nav>
            <ul>
                <li><a href="index.php">Logout</a></li>
                <li><a href="#problems" onclick="showTab('problems')">Problems</a></li>
                <li><a href="#submissions" onclick="showTab('submissions')">Submissions</a></li>
                <li><a href="https://leetcode.com/u/<?= htmlspecialchars($username) ?>/">Go to Leetcode</a></li>
            </ul>
        </nav>

        <section id="problems" class="tab-content active">
            <h2>Problems</h2>
            <div class="form-container">
                <form method="GET" action="dashboard.php">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                    <select name="tag">
                        <option value="">All Tags</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= htmlspecialchars($tag) ?>" <?= $filteredTag == $tag ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tag) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filter</button>
                </form>
            </div>
            <ul>
                <?php foreach ($problems as $problem): ?>
                    <?php
                    $status = isset($submissionMap[$problem['title_slug']]) ?
                              ($submissionMap[$problem['title_slug']] == 'Accepted' ? '✔' : '✘') : '';
                    $memoLabel = $problem['memo'] ? 'Show Memo' : 'Memo';
                    ?>
                    <li>
                        <a href="https://leetcode.com/problems/<?= htmlspecialchars($problem['title_slug']) ?>" target="_blank">
                            <?= htmlspecialchars($problem['title']) ?>
                        </a> - <?= htmlspecialchars($problem['difficulty']) ?>
                        <?= $status ?>
                        <button id="memo-button-<?= htmlspecialchars($problem['title_slug']) ?>" onclick="openMemo('<?= htmlspecialchars($problem['title_slug']) ?>')"><?= $memoLabel ?></button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPagesProblems; $i++): ?>
                    <a href="?username=<?= urlencode($username) ?>&tag=<?= urlencode($filteredTag) ?>&pageProblems=<?= $i ?>&pageSubmissions=<?= $pageSubmissions ?>" class="<?= $i == $pageProblems ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </section>

        <section id="submissions" class="tab-content">
            <h2>Submissions</h2>
            <ul>
                <?php foreach ($submissions as $submission): ?>
                    <?php
                    $statusClass = $submission['status'] == 'Accepted' ? 'status-accepted' : 'status-rejected';
                    $iconClass = $submission['status'] == 'Accepted' ? 'status-icon accepted' : 'status-icon rejected';
                    ?>
                    <li>
                        <span class="<?= $iconClass ?>"><?= $submission['status'] == 'Accepted' ? '✔' : '✘' ?></span>
                        <?= formatTimestamp($submission['timestamp']) ?> -
                        <?= htmlspecialchars($submission['title']) ?> - <span class="<?= $statusClass ?>"><?= htmlspecialchars($submission['status']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPagesSubmissions; $i++): ?>
                    <a href="?username=<?= urlencode($username) ?>&tag=<?= urlencode($filteredTag) ?>&pageProblems=<?= $pageProblems ?>&pageSubmissions=<?= $i ?>" class="<?= $i == $pageSubmissions ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </section>
    </div>

    <script>
        function showTab(tabId) {
            var tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        }

        function openMemo(titleSlug) {
            var memoWindow = window.open('memo.php?titleSlug=' + titleSlug, 'Memo', 'width=700,height=400');
        }

        function updateMemoLabel(titleSlug, label) {
            var button = document.getElementById('memo-button-' + titleSlug);
            if (button) {
                button.textContent = label;
            }
        }
    </script>
</body>
</html>
