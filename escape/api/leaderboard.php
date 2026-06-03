<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$level_id = $_GET['level_id'] ?? null;

$query = "
    SELECT u.username, l.score, l.time_elapsed
    FROM leaderboard l
    JOIN users u ON l.user_id = u.id
    WHERE u.role = 'player'
";

if ($level_id) {
    $query .= " WHERE l.level_id = ? ";
}

$query .= " ORDER BY l.score DESC LIMIT 100";

$stmt = $pdo->prepare($query);
if ($level_id) {
    $stmt->execute([$level_id]);
} else {
    $stmt->execute();
}

json_response($stmt->fetchAll());
