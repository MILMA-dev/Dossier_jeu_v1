<?php

/**
 * Send a JSON response and exit
 */
function json_response($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Sanitize output
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Calculate score based on time and hints
 */
function calculate_score($base_points, $time_elapsed, $hints_used) {
    $score = $base_points - ($time_elapsed * 2) - ($hints_used * 50);
    return max(0, $score);
}
