<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    json_response([
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'avatar' => $_SESSION['avatar']
        ]
    ]);
} else {
    json_response(['logged_in' => false], 401);
}
