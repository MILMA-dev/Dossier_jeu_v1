<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    json_response(['success' => false, 'message' => 'Non autorisé'], 401);
}

$user_id = $_POST['user_id'] ?? null;
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$role = $_POST['role'] ?? 'player';
$total_points = $_POST['total_points'] ?? 0;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

if (!$user_id || empty($username) || empty($email)) {
    json_response(['success' => false, 'message' => 'Données manquantes'], 400);
}

try {
    if ($is_active !== null) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, total_points = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $total_points, $is_active, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, total_points = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $total_points, $user_id]);
    }
    json_response(['success' => true, 'message' => 'Utilisateur mis à jour !']);
} catch (PDOException $e) {
    json_response(['success' => false, 'message' => 'Erreur technique'], 500);
}
