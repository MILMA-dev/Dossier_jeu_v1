<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'escape_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Game constants
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
