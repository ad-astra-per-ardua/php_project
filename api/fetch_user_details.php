<?php
header('Content-Type: application/json');
$username = $_GET['username'];
$apiUrl = "http://localhost:3000/$username/submission?limit=20";
$submissions = file_get_contents($apiUrl);
echo $submissions;
?>
