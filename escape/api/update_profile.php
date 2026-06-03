<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    json_response(['success' => false, 'message' => 'Non autorisé'], 401);
}

$user_id = $_SESSION['user_id'];
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$avatar = $_POST['avatar'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email)) {
    json_response(['success' => false, 'message' => 'Nom et email requis'], 400);
}

try {
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, avatar = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$username, $email, $avatar, $password_hash, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$username, $email, $avatar, $user_id]);
    }

    $_SESSION['username'] = $username;
    $_SESSION['avatar'] = $avatar;

    json_response(['success' => true, 'message' => 'Profil mis à jour !']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        json_response(['success' => false, 'message' => 'Nom ou email déjà utilisé'], 409);
    }
    json_response(['success' => false, 'message' => 'Erreur technique'], 500);
}
