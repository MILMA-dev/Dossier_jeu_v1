<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        json_response(['error' => 'Unauthorized'], 401);
    } else {
        header('Location: index.php');
        exit;
    }
}
