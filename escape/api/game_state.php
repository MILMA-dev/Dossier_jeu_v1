<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'save') {
    $user_id = $_SESSION['user_id'];
    $level_id = $_POST['level_id'];
    $current_node = $_POST['current_node'];
    $inventory = $_POST['inventory']; // JSON string
    $puzzle_states = $_POST['puzzle_states']; // JSON string
    $time_elapsed = $_POST['time_elapsed'];

    $stmt = $pdo->prepare("
        INSERT INTO game_sessions (user_id, level_id, current_node, inventory, puzzle_states, time_elapsed)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        current_node = VALUES(current_node),
        inventory = VALUES(inventory),
        puzzle_states = VALUES(puzzle_states),
        time_elapsed = VALUES(time_elapsed)
    ");
    $stmt->execute([$user_id, $level_id, $current_node, $inventory, $puzzle_states, $time_elapsed]);

    json_response(['success' => true]);
}

if ($action === 'complete') {
    $user_id = $_SESSION['user_id'];
    $level_id = $_POST['level_id'];
    $score = $_POST['score'];
    $time_elapsed = $_POST['time_elapsed'];

    // Update session status
    $stmt = $pdo->prepare("UPDATE game_sessions SET status = 'completed', finished_at = CURRENT_TIMESTAMP WHERE user_id = ? AND level_id = ? AND status = 'in_progress'");
    $stmt->execute([$user_id, $level_id]);

    // Record progress
    $stmt = $pdo->prepare("
        INSERT INTO user_progress (user_id, level_id, best_time, best_score, completed_at)
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE
        best_time = LEAST(best_time, VALUES(best_time)),
        best_score = GREATEST(best_score, VALUES(best_score)),
        completed_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$user_id, $level_id, $time_elapsed, $score]);

    // Update leaderboard
    $stmt = $pdo->prepare("INSERT INTO leaderboard (user_id, level_id, score, time_elapsed) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $level_id, $score, $time_elapsed]);

    json_response(['success' => true]);
}
